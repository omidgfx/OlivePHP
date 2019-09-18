<?php namespace Olive\Http;

use Olive\manifest;

abstract class Linker
{
    private static $src, $srcFull;

    public static function srcEx($source, $full = false, $full_port = '') {
        if ($full) return self::srcFull($source, $full_port);
        return self::src($source);
    }

    public static function srcFull($source, $port = '') {
        if (!self::$srcFull) {
            $r = manifest::ROOT_DIR;

            if ($r == '/') $r = '';
            if ($r != '') $r = $r . '/';
            $p             = Domain::isHttps() ? 's' : null;
            self::$srcFull = 'http' . $p . '://' . Domain::current() . $port . '/' . $r;
        }
        return self::$srcFull . trim($source, '/');
    }

    public static function src($source) {
        if (!self::$src) self::$src = self::push(manifest::ROOT_DIR);
        return self::$src . trim($source, '/');
    }

    private static function push($str, $char = '/', $left = true, $right = true) {
        if (!$str)
            return $str;
        //push left
        if ($left && $str[0] != $char)
            $str = $char . $str;
        //push right
        if ($right && $str[strlen($str) - 1] != $char)
            $str .= $char;
        //return
        return $str;
    }

}