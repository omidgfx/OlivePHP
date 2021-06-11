<?php namespace Olive\Exceptions;

class NotFoundException extends HttpException
{
    protected int $httpResponseCode = 404;
}