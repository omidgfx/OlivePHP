<?php namespace Olive\Http;

use Olive\manifest;

abstract class Linker
{
    private static $internalBase, $externalBase;

    public static function linkExtended($source, $external = false, $full_port = '') {
        if ($external) return self::externalLink($source, $full_port);
        return self::internalLink($source);
    }

    public static function externalLink($source, $port = '') {
        if (!self::$externalBase) {
            $r = manifest::ROOT_DIR;

            if ($r === '/') $r = '';
            if ($r !== '') $r .= '/';
            $p                  = Domain::isHttps() ? 's' : null;
            self::$externalBase = 'http' . $p . '://' . Domain::current() . $port . '/' . $r;
        }
        return self::$externalBase . trim($source, '/');
    }

    public static function internalLink($source) {
        if (!self::$internalBase) self::$internalBase = self::push(manifest::ROOT_DIR);
        return self::$internalBase . trim($source, '/');
    }

    private static function push($str, $char = '/', $left = true, $right = true) {
        if (!$str)
            return $str;
        //push left
        if ($left && $str[0] !== $char)
            $str = $char . $str;
        //push right
        if ($right && $str[strlen($str) - 1] !== $char)
            $str .= $char;
        //return
        return $str;
    }

}
