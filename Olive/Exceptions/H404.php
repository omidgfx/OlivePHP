<?php namespace Olive\Exceptions;

/**
 * Class H404 Not Found
 * @package Olive
 */
class H404 extends HttpException {
    protected $http_response_code = 404;
}
