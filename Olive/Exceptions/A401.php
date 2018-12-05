<?php namespace Olive\Exceptions;

/**
 * Class A401 Unauthorized
 * @package Olive
 */
class A401 extends ApiException {
    protected $http_response_code = 401;
}
