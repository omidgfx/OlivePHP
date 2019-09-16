<?php namespace Olive\Exceptions;

/**
 * Class H400 Bad Request
 * @package Olive
 */
class H400 extends HttpException
{
    protected $http_response_code = 400;
}
