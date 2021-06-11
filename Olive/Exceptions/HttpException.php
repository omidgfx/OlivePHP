<?php namespace Olive\Exceptions;

class HttpException extends OliveException
{
    protected int $httpResponseCode;
}