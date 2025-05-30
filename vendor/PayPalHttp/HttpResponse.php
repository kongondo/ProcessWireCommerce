<?php

namespace PayPalHttp;

/**
 * Class HttpResponse
 * @package PayPalHttp
 *
 * Object that holds your response details
 */
class HttpResponse
{
    /**
     * @var integer
     */
    public $statusCode;

    /**
     * @var array | string
     */
    public $result;

    /**
     * @var array
     */
    public $headers;

    // @KONGONDO TEMPORARY ADDITION {to deal with PHP 8.2...}
    // PHP Deprecated: Creation of dynamic property PayPalHttp\HttpResponse::$success is deprecated in ...\modules\PWCommerce\includes\order\PWCommerceProcessOrder.php:1896
    public $success;

    public function __construct($statusCode, $body, $headers) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->result = $body;
    }
}
