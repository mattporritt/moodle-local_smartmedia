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
 * Service client for getting AWS pricing information for the Elastic Transcode Services (ETS).
 *
 * @package     local_smartmedia
 * @author      Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright   2019 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_smartmedia;

use Aws\Pricing\PricingClient;

defined('MOODLE_INTERNAL') || die;

global $CFG;
// Autoload the SDK for AWS service usage.
require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');

/**
 * A client for getting pricing information for AWS Elastic Transcode Services.
 *
 * @package     local_smartmedia
 * @author      Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright   2019 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aws_ets_pricing_client {

    /**
     * The default filter field for getting AmazonETS pricing information.
     */
    const DEFAULT_FIELD = 'servicecode';

    /**
     * The default filter type used by AWS Pricing List API filters.
     */
    const DEFAULT_TYPE = 'TERM_MATCH';

    /**
     * The string represention an audio transcode service.
     */
    const MEDIATYPE_AUDIO = 'Audio';

    /**
     * The string represention a high definition transcode service (width >= 720).
     */
    const MEDIATYPE_HIGH_DEFINITION = 'High Definition';

    /**
     * The string represention a standard definition transcode service (width < 720).
     */
    const MEDIATYPE_STANDARD_DEFINITION = 'Standard Definition';

    /**
     * The string representing a successful transcoding result from a service.
     */
    const TRANSCODINGRESULT_SUCCESS = 'Success';

    /**
     * The ServiceCode for Amazon Elastic Transcode Services.
     */
    const SERVICE_CODE = 'AmazonETS';

    /**
     * Map of AWS region codes to location names used by \Aws\Pricing\PricingClient.
     */
    const REGION_LOCATIONS = [
        'us-east-1'      => 'US East (N. Virginia)',
        'us-west-1'      => 'US West (N. California)',
        'us-west-2'      => 'US West (Oregon)',
        'ap-northeast-1' => 'Asia Pacific (Tokyo)',
        'ap-south-1'     => 'Asia Pacific (Mumbai)',
        'ap-southeast-1' => 'Asia Pacific (Singapore)',
        'ap-southeast-2' => 'Asia Pacific (Sydney)',
        'eu-west-1'      => 'EU (Ireland)',
    ];

    /**
     * @var \Aws\Pricing\PricingClient
     */
    private $pricingclient;


    /**
     * aws_ets_pricing_client constructor.
     *
     * @param \Aws\Pricing\PricingClient $pricingclient the client for making Pricing List API Calls.
     */
    public function __construct(PricingClient $pricingclient) {
        $this->pricingclient = $pricingclient;
    }

    /**
     * Default filters to get all Elastic Transcode Service products.
     *
     * @return array of filter structures with the default filter values for getting AWS Pricing List information.
     * See https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-pricing-2017-10-15.html#shape-filter for filter structure.
     */
    private function get_default_product_filters() {
        return [
            [
                'Field' => self::DEFAULT_FIELD,
                'Type' => self::DEFAULT_TYPE,
                'Value' => self::SERVICE_CODE,
            ],
        ];
    }

    /**
     * Get all available Amazon Elastic Transcode Service products.
     *
     * @param array $filters of filter structures to be included for filtering products retrieved.
     * See https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-pricing-2017-10-15.html#shape-filter for filter structure.
     *
     * @return array $products of \local_smartmedia\aws_ets_product.
     */
    public function get_products($filters = []) {
        $params = [];
        // Ensure we are only looking for Amazon ETS services.
        $params['ServiceCode'] = self::SERVICE_CODE;
        $params['Filters'] = array_merge($this->get_default_product_filters(), $filters);

        $result = $this->pricingclient->getProducts($params);
        $products = [];
        foreach ($result->get('PriceList') as $product) {
             $products[] = new aws_ets_product($product);
        }
        return $products;
    }

    /**
     * Get a description of this service and it's attributes.
     *
     * @return \stdClass $description object describing this service.
     */
    public function describe_service() {

        // Ensure we are only looking for Amazon ETS services.
        $params = ['ServiceCode' => self::SERVICE_CODE];

        $result = $this->pricingclient->describeServices($params);
        $services = $result->get('Services');
        $service = reset($services);
        $description = (object) $service;
        return $description;
    }

    /**
     * Get a list of attribute values.
     *
     * @param string $attributename the attribute to get value(s) for.
     *
     * @return array $attributevalues array of values.
     */
    public function get_attribute_values($attributename) {
        $attributevalues = [];

        // Set up the required parameters for the Pricing Client query.
        $params = [];
        $params['AttributeName'] = $attributename;
        // Ensure we are only looking for Amazon ETS services.
        $params['ServiceCode'] = self::SERVICE_CODE;

        $result = $this->pricingclient->getAttributeValues($params);
        $values = $result->get('AttributeValues');

        foreach ($values as $value) {
            $attributevalues[] = $value['Value'];
        }

        return $attributevalues;
    }

    /**
     * Get the pricing for a specific transcode location.
     *
     * @param string $region the region code of an AmazonETS location to get pricing for.
     *
     * @return \local_smartmedia\location_transcode_pricing $locationpricing object containing pricing.
     */
    public function get_location_pricing($region) {
        $locationpricing = new location_transcode_pricing($region);

        // Filter products by location.
        $locationfilter = ['Field' => 'location', 'Type' => self::DEFAULT_TYPE, 'Value' => self::REGION_LOCATIONS[$region]];
        // Filter only working transcode services.
        $transcodingresultfilter = ['Field' => 'transcodingResult', 'Type' => self::DEFAULT_TYPE, 'Value' => self::TRANSCODINGRESULT_SUCCESS];
        $products = $this->get_products([$locationfilter, $transcodingresultfilter]);

        foreach ($products as $product) {
            $productfamily = $product->get_productfamily();
            switch ($productfamily) {
                case self::MEDIATYPE_STANDARD_DEFINITION :
                    $locationpricing->set_sd_pricing($product->get_transcodecost());
                    break;
                case self::MEDIATYPE_HIGH_DEFINITION :
                    $locationpricing->set_hd_pricing($product->get_transcodecost());
                    break;
                default :
                    $locationpricing->set_audio_pricing($product->get_transcodecost());
                    break;
            }
        }
        return $locationpricing;
    }
}
