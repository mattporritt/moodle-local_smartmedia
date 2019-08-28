<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class for smart media conversion operations.
 *
 * @package     local_smartmedia
 * @copyright   2019 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_smartmedia;

defined('MOODLE_INTERNAL') || die();

use Aws\S3\Exception\S3Exception;

/**
 * Class for smart media conversion operations.
 *
 * @package     local_smartmedia
 * @copyright   2019 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class conversion {

    /**
     * Smart media conversion finished without error.
     *
     * @var integer
     */
    public const CONVERSION_FINISHED = 200;

    /**
     * Smart media conversion is in progres.
     *
     * @var integer
     */
    public const CONVERSION_IN_PROGRESS = 201;

    /**
     * Smart media conversion job has been created but processing has not yet started.
     *
     * @var integer
     */
    public const CONVERSION_ACCEPTED = 202;

    /**
     * No smart media conversion record found.
     *
     * @var integer
     */
    public const CONVERSION_NOT_FOUND = 404;

    /**
     * Smart media conversion finished with error.
     *
     * @var integer
     */
    public const CONVERSION_ERROR = 500;

    /**
     * Max files to get from Moodle files table per processing run.
     *
     * @var integer
     */
    private const MAX_FILES = 1000;

    /**
     * The message states we want to check for in messages received from the SQS queue.
     * We only care about successes and failures.
     * In normal operation we ignore progress and other messages.
     *
     * @var array
     */
    private const SQS_MESSAGE_STATES = array(
        'SUCCEEDED', // Rekognition success status.
        'COMPLETED', // Elastic Transcoder success status.
        'ERROR', // Elastic Transcoder error status.
    );

    /**
     * The mapping betweeen what AWS calls the service events and their corresponding DB field names.
     *
     * @var array
     */
    private const SERVICE_MAPPING = array(
        'elastic_transcoder' => array('transcoder_status'),
        'StartLabelDetection' => array('rekog_label_status', 'Labels'),
        'StartContentModeration' => array('rekog_moderation_status', 'ModerationLabels'),
        'StartFaceDetection' => array('rekog_face_status', 'Faces'),
        'StartPersonTracking' => array('rekog_person_status', 'Persons'),

    );

    /**
     * Class constructor
     */
    public function __construct() {
        $this->config = get_config('local_smartmedia');
    }


    /**
     *  Get the configured transcoding presets as an array.
     *
     * @return array $idarray Trimmed array of transcoding presets.
     */
    private function get_preset_ids() : array {
        $rawids = $this->config->transcodepresets; // Get the raw ids.
        $untrimmedids = explode(',', $rawids); // Split ids into an array of strings by comma.
        $idarray = array_map('trim', $untrimmedids); // Remove whitespace from each id in array.

        return $idarray;
    }

    /**
     * Given a conversion id create records for each configured transcoding preset id,
     * ready to be stored in the Moodle database.
     *
     * @param int $convid The conversion id to create the preset records for.
     * @return array $presetrecords The preset records to insert into the Moodle database.
     */
    private function get_preset_records(int $convid) : array {
        $presetrecords = array();
        $presetids = $this->get_preset_ids();

        foreach ($presetids as $presetid) {
            $record = new \stdClass();
            $record->convid = $convid;
            $record->preset = $presetid;

            $presetrecords[] = $record;
        }

        return $presetrecords;
    }

    /**
     * Create the smart media conversion record.
     * These records will be processed by a scheduled task.
     *
     * @param \stored_file $file The file object to create the converion for.
     */
    private function create_conversion(\stored_file $file) : void {
        global $DB;
        $now = time();
        $convid = 0;

        $cnvrec = new \stdClass();
        $cnvrec->pathnamehash = $file->get_pathnamehash();
        $cnvrec->contenthash = $file->get_contenthash();
        $cnvrec->status = $this::CONVERSION_ACCEPTED;
        $cnvrec->transcoder_status = $this::CONVERSION_ACCEPTED;
        $cnvrec->transcribe_status =
            $this->config->transcribe == 1 ? $this::CONVERSION_ACCEPTED : $this::CONVERSION_NOT_FOUND;
        $cnvrec->rekog_label_status =
            $this->config->detectlabels == 1 ? $this::CONVERSION_ACCEPTED : $this::CONVERSION_NOT_FOUND;
        $cnvrec->rekog_moderation_status =
            $this->config->detectmoderation == 1 ? $this::CONVERSION_ACCEPTED : $this::CONVERSION_NOT_FOUND;
        $cnvrec->rekog_face_status =
            $this->config->detectfaces == 1 ? $this::CONVERSION_ACCEPTED : $this::CONVERSION_NOT_FOUND;
        $cnvrec->rekog_person_status =
            $this->config->detectpeople == 1 ? $this::CONVERSION_ACCEPTED : $this::CONVERSION_NOT_FOUND;
        $cnvrec->detect_sentiment_status =
            $this->config->detectsentiment == 1 ? $this::CONVERSION_ACCEPTED : $this::CONVERSION_NOT_FOUND;
        $cnvrec->detect_phrases_status =
            $this->config->detectphrases == 1 ? $this::CONVERSION_ACCEPTED : $this::CONVERSION_NOT_FOUND;
        $cnvrec->detect_entities_status =
            $this->config->detectentities == 1 ? $this::CONVERSION_ACCEPTED : $this::CONVERSION_NOT_FOUND;
        $cnvrec->timecreated = $now;
        $cnvrec->timemodified = $now;

        // Race conditions mean that we could try to create a conversion record multiple times.
        // This is OK and expected, we will handle the error.
        try {
            $convid = $DB->insert_record('local_smartmedia_conv', $cnvrec);

        } catch (\dml_write_exception $e) {
            // If error is anything else but a duplicate insert, this is unexected,
            // so re-throw the error.
            if (!strpos($e->getMessage(), 'locasmarconv_pat_uix') && !strpos($e->getMessage(), 'locasmarconv_con_uix')) {
                throw $e;
            }
        }

        // If we have a valid conversion record from the insert, then create the presets record.
        // With the above logic we shouldn't get race conditions here.
        if ($convid > 0) {
            $presetrecords = $this->get_preset_records($convid);
            $DB->insert_records('local_smartmedia_presets', $presetrecords);
        }
    }

    /**
     * Get the smart media conversion status for a given resource.
     *
     * @param \stored_file $file The Moodle file object of the asset.
     * @return int $status The response status to the request.
     */
    private function get_conversion_status(\stored_file $file) : int {
        global $DB;

        $pathnamehash = $file->get_pathnamehash();
        $conditions = array('pathnamehash' => $pathnamehash);
        $status = $DB->get_field('local_smartmedia_conv', 'status', $conditions);

        if (!$status) {
            $status = self::CONVERSION_NOT_FOUND;
        }

        return $status;
    }

    /**
     * Given a Moodle URL check file exists in the Moodle file table
     * and retreive the file object.
     * This requires some horrible reverse engineering.
     *
     * @param \moodle_url $href Plugin file url to extract from.
     * @return \stored_file $file The Moodle file object.
     */
    private function get_file_from_url(\moodle_url $href) : \stored_file {
        // Extract the elements we need from the Moodle URL.
        $argumentsstring = $href->get_path(true);
        $rawarguments = explode('/', $argumentsstring);
        $pluginfileposition = array_search('pluginfile.php', $rawarguments);
        $hrefarguments = array_slice($rawarguments, ($pluginfileposition + 1));
        $argumentcount = count($hrefarguments);

        $contextid = $hrefarguments[0];
        $component = clean_param($hrefarguments[1], PARAM_COMPONENT);
        $filearea = clean_param($hrefarguments[2], PARAM_AREA);
        $filename = $hrefarguments[($argumentcount - 1)];

        // Sensible defaults for item id and filepath.
        $itemid = 0;
        $filepath = '/';

        // If item id is non zero then it will be the fourth element in the array.
        if ($argumentcount > 4 ) {
            $itemid = (int)$hrefarguments[3];
        }

        // Handle complex file paths in href.
        if ($argumentcount > 5 ) {
            $filepatharray = array_slice($hrefarguments, 4, -1);
            $filepath = '/' . implode('/', $filepatharray) . '/';
        }

        // Use the information we have extracted to get the pathname hash.
        $fs = get_file_storage();
        $file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);

        return $file;
    }

    /**
     * Get smart media for file.
     *
     * @param \moodle_url $href
     * @param bool $triggerconversion
     * @return array
     */
    public function get_smart_media(\moodle_url $href, bool $triggerconversion = false) : array {
        $smartmedia = array();

        // Split URL up into components.
        $file = $this->get_file_from_url($href);

        // Query conversion table for status.
        $conversionstatus = $this->get_conversion_status($file);

        // If no record in table and trigger conversion is true add record.
        if ($triggerconversion && $conversionstatus == self::CONVERSION_NOT_FOUND) {
            $this->create_conversion($file);
        }

        // If processing complete get all urls and data for source href.

        // TODO: Cache the result for a very long time as once processing is finished it will never change
        // and when processing is finished we will explictly clear the cache.

        return $smartmedia;

    }

    /**
     * Get conversion records to process smartmedia conversions.
     *
     * @param int $status Status of records to get.
     * @return array $filerecords Records to process.
     */
    private function get_conversion_records(int $status) : array {
        global $DB;

        $conditions = array('status' => $status);
        $limit = self::MAX_FILES;
        $fields = 'id, pathnamehash, contenthash, status, transcoder_status, transcribe_status,
                  rekog_label_status, rekog_moderation_status, rekog_face_status, rekog_person_status,
                  detect_sentiment_status, detect_phrases_status, detect_entities_status';

        $filerecords = $DB->get_records('local_smartmedia_conv', $conditions, '', $fields, 0, $limit);

        return $filerecords;
    }

    /**
     * Get the configured covnersion for this conversion record in a format that will
     * be sent to AWS for processing.
     *
     * @param \stdClass $conversionrecord The cponversion record to get the settings for.
     * @return array $settings The conversion record settings.
     */
    private function get_convserion_settings(\stdClass $conversionrecord) : array {
        global $DB, $CFG;
        $settings = array();

        // Metadata space per S3 object is limited so do some dirty encoding
        // of the processes we want to carry out on the file. These are
        // interpereted on the AWS side.

        $processes = '';
        $processes .= $conversionrecord->transcribe_status == self::CONVERSION_ACCEPTED ? '1' : '0';
        $processes .= $conversionrecord->rekog_label_status == self::CONVERSION_ACCEPTED ? '1' : '0';
        $processes .= $conversionrecord->rekog_moderation_status == self::CONVERSION_ACCEPTED ? '1' : '0';
        $processes .= $conversionrecord->rekog_face_status == self::CONVERSION_ACCEPTED ? '1' : '0';
        $processes .= $conversionrecord->rekog_person_status == self::CONVERSION_ACCEPTED ? '1' : '0';
        $processes .= $conversionrecord->detect_sentiment_status == self::CONVERSION_ACCEPTED ? '1' : '0';
        $processes .= $conversionrecord->detect_phrases_status == self::CONVERSION_ACCEPTED ? '1' : '0';
        $processes .= $conversionrecord->detect_entities_status == self::CONVERSION_ACCEPTED ? '1' : '0';

        $presets = $DB->get_fieldset_select('local_smartmedia_presets', 'preset', 'convid = ?', array($conversionrecord->id));
        $prsetstring = implode(',', $presets);

        $settings['processes'] = $processes;
        $settings['presets'] = $prsetstring;
        $settings['siteid'] = $CFG->siteidentifier;

        return $settings;
    }

    /**
     * Send file for conversion processing in AWS.
     *
     * @param \stored_file $file The file to upload for conversion.
     * @param array $settings Settings to be used for file convsersion.
     * @param \GuzzleHttp\Handler|null $handler Optional handler.
     * @return int $status The status code of the upload.
     */
    private function send_file_for_processing(\stored_file $file, array $settings, $handler=null) : int {
        $awss3 = new \local_smartmedia\aws_s3();
        $s3client = $awss3->create_client($handler);

        $uploadparams = array(
            'Bucket' => $this->config->s3_input_bucket, // Required.
            'Key' => $file->get_contenthash(), // Required.
            'Body' => $file->get_content_file_handle(), // Required.
            'Metadata' => $settings
        );

        try {
            $result = $s3client->putObject($uploadparams);
            $status = self::CONVERSION_IN_PROGRESS;
        } catch (S3Exception $e) {
            $status = self::CONVERSION_ERROR;
        }

        // TODO: add event for file sending include status etc.

        return $status;

    }

    /**
     * Update conversion records in the Moodle database.
     *
     * @param array $results The result details to update the records.
     */
    private function update_conversion_records(array $results) : void {
        global $DB;

        // Check if we are going to be performing multiple inserts.
        if (count($results) > 1) {
            $expectbulk = true;
        } else {
            $expectbulk = false;
        }

        // Update the records in the database.
        foreach ($results as $key => $result) {
            $updaterecord = new \stdClass();
            $updaterecord->id = $key;
            $updaterecord->status = $result;
            $updaterecord->timemodified = time();

            $DB->update_record('local_smartmedia_conv', $updaterecord, $expectbulk);
        }
    }

    /**
     * Process not yet started conversions.
     *
     * @return array $results The results of the processing.
     */
    public function process_conversions() : array {
        global $DB;

        $results = array();
        $fs = get_file_storage();
        $conversionrecords = $this->get_conversion_records(self::CONVERSION_ACCEPTED); // Get not yet started conversion records.

        foreach ($conversionrecords as $conversionrecord) { // Itterate through not yet started records.
            $settings = $this->get_convserion_settings($conversionrecord); // Get convession settings.
            $file = $fs->get_file_by_hash($conversionrecord->pathnamehash); // Get the file to process.
            $results[$conversionrecord->id] = $this->send_file_for_processing($file, $settings); // Send for processing.
            $this->update_conversion_records($results); // Update conversion records.
        }

        return $results;
    }

    /**
     * Given a conversion record get all the messages from the sqs queue message table
     * that are for this contenthash (object id).
     * We only get "success" and "failure" messages we don't care about pending or warning messages.
     * Only check for messages relating to configured conversions for this record that haven't
     * already succeed or failed.
     *
     * @param \stdClass $conversionrecord The conversion record to get messages for.
     * @return array $queuemessages The matching queue messages.
     */
    private function get_queue_messages(\stdClass $conversionrecord) : array {
        global $DB;

        // Using the conversion record determine which services we are looking for messages from.
        // Only get messages for conversions that have not yet finished.
        $services = array();

        if ($conversionrecord->transcoder_status == self::CONVERSION_ACCEPTED
            || $conversionrecord->transcoder_status == self::CONVERSION_IN_PROGRESS) {
                $services[] = 'elastic_transcoder';
        }
        if ($conversionrecord->rekog_label_status == self::CONVERSION_ACCEPTED
            || $conversionrecord->rekog_label_status == self::CONVERSION_IN_PROGRESS) {
            $services[] = 'StartLabelDetection';
        }
        if ($conversionrecord->rekog_moderation_status == self::CONVERSION_ACCEPTED
            || $conversionrecord->rekog_moderation_status == self::CONVERSION_IN_PROGRESS) {
            $services[] = 'StartContentModeration';
        }
        if ($conversionrecord->rekog_face_status == self::CONVERSION_ACCEPTED
            || $conversionrecord->rekog_face_status == self::CONVERSION_IN_PROGRESS) {
            $services[] = 'StartFaceDetection';
        }
        if ($conversionrecord->rekog_person_status == self::CONVERSION_ACCEPTED
            || $conversionrecord->rekog_person_status == self::CONVERSION_IN_PROGRESS) {
            $services[] = 'StartPersonTracking';
        }

        // Get all queue messages for this object.
        list($processinsql, $processinparams) = $DB->get_in_or_equal($services);
        list($statusinsql, $statusinparams) = $DB->get_in_or_equal(self::SQS_MESSAGE_STATES);
        $params = array_merge($processinparams, $statusinparams);
        $params[] = $conversionrecord->contenthash;

        $sql = "SELECT *
                  FROM {local_smartmedia_queue_msgs}
                 WHERE process $processinsql
                       AND status $statusinsql
                       AND objectkey = ?";
        $queuemessages = $DB->get_records_sql($sql, $params);

        return $queuemessages;
    }

    /**
     * Get the transcoded media files from AWS S3,
     *
     * @param \stdClass $conversionrecord The conversion record from the database.
     * @param \GuzzleHttp\Handler|null $handler Optional handler.
     */
    private function get_transcode_files(\stdClass $conversionrecord, $handler=null) : void {
        $awss3 = new \local_smartmedia\aws_s3();
        $s3client = $awss3->create_client($handler);

        // Transcoding could have made many files, but the job only calls success when all files are generated.
        // So first we get a list of the files.
        $listparams = array(
                'Bucket' => $this->config->s3_output_bucket,
                'MaxKeys' => 1000,  // The maximum allowed before we need to page, we should NEVER have this many.
                'Prefix' => $conversionrecord->contenthash . '/conversions/',  // Location in the S3 bucket where the files live.
        );
        $availableobjects = $s3client->listObjects($listparams);

        // Then we itterate over that list and get all the files available.
        $fs = get_file_storage();
        foreach ($availableobjects['Contents'] as $availableobject) {
            $filerecord = array(
                'contextid' => 1, // Put files in the site level context as they aren't associated with a specific context.
                'component' => 'local_smartmedia',
                'filearea' => 'media',
                'itemid' => 0,
                'filepath' => '/' . $conversionrecord->contenthash . '/conversions/',
                'filename' => basename($availableobject['Key'])

            );

            $downloadparams = array(
                    'Bucket' => $this->config->s3_output_bucket, // Required.
                    'Key' => $availableobject['Key'], // Required.
            );

            $getobject = $s3client->getObject($downloadparams);

            $tmpfile = tmpfile();
            fwrite($tmpfile, $getobject['Body']);
            $tmppath = stream_get_meta_data($tmpfile)['uri'];

            $fs->create_file_from_pathname($filerecord, $tmppath);
            fclose($tmpfile);
        }

        // TODO: Also remove files from AWS.
    }

    /**
     * Get the file from AWS for a given conversion process.
     *
     * @param \stdClass $conversionrecord The conversion record from the database.
     * @param string $process The process to get the file for.
     * @param \GuzzleHttp\Handler|null $handler Optional handler.
     */
    private function get_data_file(\stdClass $conversionrecord, string $process, $handler=null) {
        $awss3 = new \local_smartmedia\aws_s3();
        $s3client = $awss3->create_client($handler);

        $objectkey = self::SERVICE_MAPPING[$process][1];
        $fs = get_file_storage();

        $filerecord = array(
            'contextid' => 1, // Put files in the site level context as they aren't associated with a specific context.
            'component' => 'local_smartmedia',
            'filearea' => 'metadata',
            'itemid' => 0,
            'filepath' => '/' . $conversionrecord->contenthash . '/metadata/',
            'filename' => $objectkey . '.json'
        );

        $downloadparams = array(
                'Bucket' => $this->config->s3_output_bucket, // Required.
                'Key' => $conversionrecord->contenthash . '/metadata/' . $objectkey . '.json', // Required.
        );

        $getobject = $s3client->getObject($downloadparams);

        $tmpfile = tmpfile();
        fwrite($tmpfile, $getobject['Body']);
        $tmppath = stream_get_meta_data($tmpfile)['uri'];

        $fs->create_file_from_pathname($filerecord, $tmppath);
        fclose($tmpfile);

        // TODO: Also remove files from AWS.
    }

    /**
     * Process the conversion records and get the files from AWS.
     *
     * @param \stdClass $conversionrecord The conversion record from the database.
     * @param array $queuemessages Quemessages from the database relating to this conversion record.
     * @param \GuzzleHttp\Handler|null $handler Optional handler.
     * @return \stdClass $conversionrecord The updated conversion record.
     */
    private function process_conversion(\stdClass $conversionrecord, array $queuemessages, $handler=null) : \stdClass {
        global $DB;

        foreach ($queuemessages as $message) {
            if ($message->status == 'ERROR' && $message->process == 'elastic_transcoder') {
                // If Elastic Transcoder conversion has failed then all other conversions have also failed.
                // It is also highly likely this will be the only message recevied.
                $conversionrecord->status = self::CONVERSION_ERROR;
                $conversionrecord->transcoder_status = self::CONVERSION_ERROR;
                $conversionrecord->rekog_label_status = self::CONVERSION_ERROR;
                $conversionrecord->rekog_moderation_status = self::CONVERSION_ERROR;
                $conversionrecord->rekog_face_status = self::CONVERSION_ERROR;
                $conversionrecord->rekog_person_status = self::CONVERSION_ERROR;
                $conversionrecord->timecreated = time();
                $conversionrecord->timecompleted = time();

                break;

            } else if ($message->status == 'COMPLETED' || $message->status == 'SUCCEEDED') {
                // For each successful status get the file/s for the conversion.
                if ($message->process == 'elastic_transcoder') {
                    // Get Elastic Transcoder files.
                    $this->get_transcode_files($conversionrecord, $handler);

                    $conversionrecord->transcoder_status = self::CONVERSION_FINISHED;

                } else {
                    // Get other process data files.
                    $this->get_data_file($conversionrecord, $message->process, $handler);

                    $statusfield = self::SERVICE_MAPPING[$message->process][0];
                    $conversionrecord->{$statusfield} = self::CONVERSION_FINISHED;
                }

            } else if ($message->status == 'ERROR') {
                // For each failed status mark it as failed in the record.
                $statusfield = self::SERVICE_MAPPING[$message->process][0];
                $conversionrecord->{$statusfield} = self::CONVERSION_ERROR;
            }
        }

        // Update the database with the modified conversion record.
        $DB->update_record('local_smartmedia_conv', $conversionrecord);

        return $conversionrecord;
    }

    /**
     * Update the overall completion status for a completion record.
     * Overall conversion record is finished when all the individual conversions are finished.
     *
     *
     * @param \stdClass $updatedrecord The record to check the completion status for.
     * @return \stdClass $updatedrecord The updated completion record.
     */
    private function update_completion_status(\stdClass $updatedrecord) : \stdClass {
        global $DB;

        // Only set the final completion status if all other processes are finished.
        if (($updatedrecord->transcoder_status == self::CONVERSION_FINISHED
                || $updatedrecord->transcoder_status == self::CONVERSION_NOT_FOUND )
            && ($updatedrecord->rekog_label_status == self::CONVERSION_FINISHED
                || $updatedrecord->rekog_label_status == self::CONVERSION_NOT_FOUND)
            && ($updatedrecord->rekog_moderation_status == self::CONVERSION_FINISHED
                || $updatedrecord->rekog_moderation_status == self::CONVERSION_NOT_FOUND)
            && ($updatedrecord->rekog_face_status == self::CONVERSION_FINISHED
                || $updatedrecord->rekog_face_status == self::CONVERSION_NOT_FOUND)
            && ($updatedrecord->rekog_person_status == self::CONVERSION_FINISHED)
                || $updatedrecord->rekog_person_status == self::CONVERSION_NOT_FOUND) {

                $updatedrecord->status = self::CONVERSION_FINISHED;
                $updatedrecord->timemodified = time();
                $updatedrecord->timecompleted = time();
                // Update the database with the modified conversion record.

                $DB->update_record('local_smartmedia_conv', $updatedrecord);

                // TODO: Also delete file from AWS.
        }

        return $updatedrecord;
    }

    /**
     * Update pending conversions.
     *
     * @return array $results The results of the processing.
     */
    public function update_pending_conversions() : array {
        global $DB;

        $results = array();
        $conversionrecords = $this->get_conversion_records(self::CONVERSION_IN_PROGRESS); // Get pending conversion records.

        foreach ($conversionrecords as $conversionrecord) { // Itterate through pending records.

            // Get recevied messages for this conversion record that are not related to already completed processes.
            $queuemessages = $this->get_queue_messages($conversionrecord);

            // Process the messages and get files from AWS as required.
            $updatedrecord = $this->process_conversion($conversionrecord, $queuemessages);

            // If all conversions have reached a final state (complete or failed) update overall conversion status.
            $results[] = $this->update_completion_status($updatedrecord);

        }

        return $results;
    }

    /**
     * Get the pathnamehases for files that have metadata extracted,
     * but that do not have conversion records.
     *
     * @return array $pathnamehashes Array of pathnamehashes.
     */
    private function get_pathnamehashes() : array {
        global $DB;

        $limit = self::MAX_FILES;
        $sql = "SELECT lsd.id, lsd.pathnamehash
                  FROM {local_smartmedia_data} lsd
             LEFT JOIN {local_smartmedia_conv} lsc ON lsd.contenthash = lsc.contenthash
                 WHERE lsc.contenthash IS NULL";
        $pathnamehashes = $DB->get_records_sql($sql, null, 0, $limit);

        return $pathnamehashes;

    }

    /**
     * Create conversion records for files that have metadata,
     * but don't have conversion records.
     *
     * @return array
     */
    public function create_conversions() : array {
        $pathnamehashes = $this->get_pathnamehashes(); // Get pathnamehashes for conversions.
        $fs = get_file_storage();

        foreach ($pathnamehashes as $pathnamehash) {
            $file = $fs->get_file_by_hash($pathnamehash->pathnamehash);
            $this->create_conversion($file);
        }

        return $pathnamehashes;
    }

}