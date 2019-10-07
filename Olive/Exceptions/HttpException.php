<?php namespace Olive\Exceptions;

class HttpException extends OliveException
{
    protected $http_response_code = 0;

    public function __construct($message = '', $code = null, OliveException $previous = null) {
        $this->code = $code ?? $this->http_response_code;
        parent::__construct($message, $this->code, $previous);
    }

}
