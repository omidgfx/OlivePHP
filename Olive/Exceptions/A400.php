<?php namespace Olive\Exceptions;

/**
 * Class A400 Bad Request
 * @package Olive
 */
class A400 extends ApiException {
    protected $http_response_code = 400;
}
