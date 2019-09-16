<?php namespace Olive\Exceptions;

/**
 * Class H401 Unauthorized
 * @package Olive
 */
class H401 extends HttpException
{
    protected $http_response_code = 401;
}