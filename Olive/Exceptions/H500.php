<?php namespace Olive\Exceptions;

/**
 * Class H500 Internal Server Error
 * @package Olive
 */
class H500 extends HttpException
{
    protected $http_response_code = 500;

}