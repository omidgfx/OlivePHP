<?php namespace Olive\Http;

use Olive\Contracts\URL;

abstract class Server
{
    public static function domain(): string {
        return $_SERVER['SERVER_NAME'];
    }

    public static function isSSL(): bool {
        $https = strtolower((string)($_SERVER['HTTPS'] ?? 0));
        $port  = self::port();
        return $https === 'on' || $https === '1' || $port === 443;
    }

    public static function scheme(): string {
        return self::isSSL() ? 'https' : 'http';
    }

    public static function port(): int {
        return (int)($_SERVER['SERVER_PORT'] ?? 80);
    }


    public static function domainURL(): URL {
        $u = new URL;
        $u->setScheme(self::scheme())
            ->setHost(self::domain());
        $port = self::port();
        if ($port !== 80 && $port !== 443)
            $u->setPort($port);
        return $u;
    }
}
