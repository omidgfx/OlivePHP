<?php namespace Olive\Exceptions;


/**
 * Class H501 Not Implemented
 * @package Olive
 */
class H501 extends HttpException
{
    protected $http_response_code = 501;
}
