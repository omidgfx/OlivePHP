<?php namespace Olive\Exceptions;

class OliveException extends \Exception {
    protected $code = null;
}

class HttpException extends OliveException {
    protected $http_response_code = 0;

    function __construct($message = "", $code = null, OliveException $previous = null) {
        $this->code = $code !== null ? $code : $this->http_response_code;

        parent::__construct($message, $this->code, $previous);
    }

}

class OliveError extends OliveException {
    public $code, $file, $line, $message;
}

class OliveFatalError extends OliveError {

}

/**
 * Class H400 Bad Request
 * @package Olive
 */
class H400 extends HttpException {
    protected $http_response_code = 400;
}

/**
 * Class H401 Unauthorized
 * @package Olive
 */
class H401 extends HttpException {
    protected $http_response_code = 401;
}

/**
 * Class H402 Payment Request
 * @package Olive
 */
class H402 extends HttpException {
    protected $http_response_code = 402;

}

/**
 * Class H403 Forbidden
 * @package Olive
 */
class H403 extends HttpException {
    protected $http_response_code = 403;
}

/**
 * Class H404 Not Found
 * @package Olive
 */
class H404 extends HttpException {
    protected $http_response_code = 404;
}

/**
 * Class H500 Internal Server Error
 * @package Olive
 */
class H500 extends HttpException {
    protected $http_response_code = 500;

}

/**
 * Class H501 Not Implemented
 * @package Olive
 */
class H501 extends HttpException {
    protected $http_response_code = 501;
}

/**
 * Class H503 Service Unavailable
 * @package Olive
 */
class H503 extends HttpException {
    protected $http_response_code = 503;

}


abstract class ApiException extends HttpException {

}

/**
 * Class A400 Bad Request
 * @package Olive
 */
class A400 extends ApiException {
    protected $http_response_code = 400;
}

/**
 * Class A401 Unauthorized
 * @package Olive
 */
class A401 extends ApiException {
    protected $http_response_code = 401;
}

/**
 * Class A402 Payment Request
 * @package Olive
 */
class A402 extends ApiException {
    protected $http_response_code = 402;

}

/**
 * Class A403 Forbidden
 * @package Olive
 */
class A403 extends ApiException {
    protected $http_response_code = 403;
}

/**
 * Class A404 Not Found
 * @package Olive
 */
class A404 extends ApiException {
    protected $http_response_code = 404;
}

/**
 * Class A500 Internal Server Error
 * @package Olive
 */
class A500 extends ApiException {
    protected $http_response_code = 500;

}

/**
 * Class A501 Not Implemented
 * @package Olive
 */
class A501 extends ApiException {
    protected $http_response_code = 501;
}

/**
 * Class A503 Service Unavailable
 * @package Olive
 */
class A503 extends ApiException {
    protected $http_response_code = 503;

}


class CSRFTokenInvalid extends H403 {

}

class CSRFTokenExpired extends H403 {

}

class URLException extends H500 {

}

class ValidatorException extends OliveException {

}