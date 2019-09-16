<?php namespace Olive\Exceptions;

/**
 * Class A500 Internal Server Error
 * @package Olive
 */
class A500 extends ApiException
{
    protected $http_response_code = 500;
}
