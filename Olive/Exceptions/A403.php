<?php namespace Olive\Exceptions;

/**
 * Class A403 Forbidden
 * @package Olive
 */
class A403 extends ApiException {
    protected $http_response_code = 403;
}
