<?php namespace Olive\Http;

class Domain
{
    public static function current() {
        return $_SERVER['SERVER_NAME'];
    }

    public static function isHttps() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }
}
