<?php namespace Olive\Http;

abstract class Middleware
{
    abstract public static function handle($request, $variables): bool|Response;
}