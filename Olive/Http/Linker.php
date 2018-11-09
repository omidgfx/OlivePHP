<?php namespace Olive\Http;

use Olive\manifest;

abstract class Linker {
    private static $src, $srcFull;

    public static function src($source) {
        if(!self::$src) self::$src = self::push(manifest::ROOT_DIR);
        return self::$src . trim($source, '/');
    }

    public static function srcFull($source, $port = '', $protocol = null) {
        if(!self::$srcFull) {
            $r = manifest::ROOT_DIR;

            if($r == '/') $r = '';
            if($r != '') $r = $r . '/';
            $p             = $protocol == null ? (self::isConnectionSecure() ? 's' : null) : $protocol;
            self::$srcFull = 'http' . $p . '://' . manifest::DOMAIN . $port . '/' . $r;
        }
        return self::$srcFull . trim($source, '/');
    }

    public static function srcEx($source, $full = false, $full_port = '', $full_protocol = null) {
        if($full) return self::srcFull($source, $full_port, $full_protocol);
        return self::src($source);
    }

    private static function isConnectionSecure() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || $_SERVER['SERVER_PORT'] == 443;
    }


    private static function push($str, $char = '/', $left = true, $right = true) {
        if(!$str)
            return $str;
        //push left
        if($left && $str[0] != $char)
            $str = $char . $str;
        //push right
        if($right && $str[strlen($str) - 1] != $char)
            $str .= $char;

        //return
        return $str;
    }

}