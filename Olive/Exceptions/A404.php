<?php namespace Olive\Exceptions;

/**
 * Class A404 Not Found
 * @package Olive
 */
class A404 extends ApiException {
    protected $http_response_code = 404;
}
