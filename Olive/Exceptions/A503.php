<?php namespace Olive\Exceptions;

/**
 * Class A503 Service Unavailable
 * @package Olive
 */
class A503 extends ApiException
{
    protected $http_response_code = 503;
}
