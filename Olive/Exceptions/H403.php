<?php namespace Olive\Exceptions;


/**
 * Class H403 Forbidden
 * @package Olive
 */
class H403 extends HttpException {
    protected $http_response_code = 403;
}
