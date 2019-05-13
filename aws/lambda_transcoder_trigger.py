'''
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

@copyright   2019 Matt Porritt <mattp@catalyst-au.net>
@license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

'''

import boto3
import botocore
import os
import logging
import io
from botocore.exceptions import ClientError

s3_client = boto3.client('s3')
et_client = boto3.client('elastictranscoder')
logger = logging.getLogger()


def submit_transcode_jobs(s3key, pipeline_id):
    """
    Submits jobs to Elastic Transcoder.
    """

    logger.info('Triggering transcode job...')

    response = et_client.create_job(
        PipelineId=pipeline_id,
         OutputKeyPrefix=s3key + '/conversions/',
         Input={
            'Key': s3key,
        },
        Outputs=[
            {
                'Key': '{}.mp4'.format(s3key),
                'PresetId': '1351620000001-100070',  # System preset: Facebook, SmugMug, Vimeo, YouTube
                'ThumbnailPattern': '',
            },
            {
                'Key': '{}.webm'.format(s3key),
                'PresetId': '1351620000001-100240',  # System preset: Webm 720p
                'ThumbnailPattern': '',
             },
            {
                'Key': '{}.mp3'.format(s3key),
                'PresetId': '1351620000001-300020',  # System preset: Audio MP3 - 192 kilobits/second
                'ThumbnailPattern': '',
             },
        ]
    )

    logger.info(response)


def lambda_handler(event, context):
    """
    lambda_handler is the entry point that is invoked when the lambda function is called,
    more information can be found in the docs:
    https://docs.aws.amazon.com/lambda/latest/dg/python-programming-model-handler-types.html

    Trigger the file conversion when the source file is uploaded to the input s3 bucket.
    """

    #  Set logging
    logging_level = os.environ.get('LoggingLevel', logging.ERROR)
    logger.setLevel(int(logging_level))

    logging.info(event)

    #  Get Pipeline ID from environment variable
    pipeline_id = os.environ.get('PipelineId')
    logger.info('Executing Pipeline: {}'.format(pipeline_id))

    #  Now get and process the file from the input bucket.
    for record in event['Records']:
        bucket = record['s3']['bucket']['name']
        key = record['s3']['object']['key']

        #  Filter out permissions check file.
        #  This is initiated by Moodle to check bucket access is correct
        if key == 'permissions_check_file':
            continue

        logger.info('File uploaded: {}'.format(key))

        submit_transcode_jobs(key, pipeline_id)

