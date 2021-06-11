<?php namespace Olive\Http;

use manifest;
use Olive\Contracts\URL;

abstract class Link
{
    private static ?URL $externalBase = null;
    private static ?URL $internalBase = null;


    public static function external(string $source, $asURL = false): string|URL {
        if (!self::$externalBase)
            self::$externalBase = Server::domainURL()->append(manifest::root_path);

        $u = clone self::$externalBase;
        $u->append($source);
        return $asURL ? $u : (string)$u;
    }

    public static function internal(string $source, $asURL = false): string|URL {
        if (!self::$internalBase) {
            self::$internalBase = (new URL())
                ->append(manifest::root_path);
        }
        $u = clone self::$internalBase;
        $u->append($source);
        return $asURL ? $u : (string)$u;
    }
}