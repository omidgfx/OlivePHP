<?php namespace Olive\Exceptions;


/**
 * Class H503 Service Unavailable
 * @package Olive
 */
class H503 extends HttpException
{
    protected $http_response_code = 503;

}
