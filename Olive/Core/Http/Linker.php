<?php namespace Olive\Http;

use Olive\manifest;

abstract class Linker {
    private static $src, $srcFull;

    public static function src($source) {
        if(!self::$src) self::$src = self::push(manifest::ROOT);
        return self::$src . trim($source, '/');
    }

    public static function srcFull($source, $port = '', $protocol = NULL) {
        if(!self::$srcFull) {
            $r = manifest::ROOT;

            if($r == '/') $r = '';
            if($r != '') $r = $r . '/';
            $p             = $protocol == NULL ? (self::isConnectionSecure() ? 's' : NULL) : $protocol;
            self::$srcFull = 'http' . $p . '://' . manifest::HOST . $port . '/' . $r;
        }
        return self::$srcFull . trim($source, '/');
    }

    /**
     * ## Parse given `$url`
     * * **Strings** _returns:_ `src($url)`
     * * **Escaped strings** (strings they starts with back-slash \\) _returns:_ `substr($url, 1)`
     * * **Array** [string,bool] with 2 elements _returns:_ `src(string, bool)`
     * @param string|array $url
     * @return string
     */
    public static function parse($url) {
        if(is_array($url)) {
            if(count($url) == 1)
                $url[] = FALSE;
            return src($url[0], $url[1]);
        }
        return $url[0] == '\\' ? substr($url, 1) : src($url);
    }

    public static function srcEx($source, $full = FALSE, $full_port = '', $full_protocol = NULL) {
        if($full) return self::srcFull($source, $full_port, $full_protocol);
        return self::src($source);
    }

    private static function isConnectionSecure() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || $_SERVER['SERVER_PORT'] == 443;
    }


    private static function push($str, $char = '/', $left = TRUE, $right = TRUE) {
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