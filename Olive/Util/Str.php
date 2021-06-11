<?php namespace Olive\Util;

use Exception;
use manifest;

abstract class Str
{
    /**
     * Limit given text to numbers of line
     *
     * @param        $str
     * @param int $num
     * @param string $ellipsis
     *
     * @return string
     */
    public static function limitLines($str, int $num = 5, string $ellipsis = '…'): string {
        $lines  = explode("\n", $str);
        $firsts = array_slice($lines, 0, $num);

        return implode("\n", $firsts) . ((count($lines) > $num && $ellipsis !== null) ? $ellipsis : null);
    }

    /**
     * @param string|string[] $search
     * @param $text
     *
     * @param bool $ignoreCase
     *
     * @return bool
     */
    public static function startsWith(array|string $search, $text, bool $ignoreCase = false): bool {

        if (is_array($search)) {
            foreach ($search as $d)
                if (self::startsWith($d, $text, $ignoreCase)) return true;

            return false;
        }
        $search = $ignoreCase ? strtolower($search) : $search;
        $text   = $ignoreCase ? strtolower($text) : $text;

        return $search === '' || mb_strrpos($text, $search, -mb_strlen($text)) !== false;

    }

    /**
     * @param string|string[] $search
     * @param $text
     *
     * @param bool $ignoreCase
     *
     * @return bool
     */
    public static function endsWith(array|string $search, $text, bool $ignoreCase = false): bool {
        if (is_array($search)) {
            foreach ($search as $d)
                if (self::endsWith($d, $text, $ignoreCase)) return true;

            return false;
        }


        $search = $ignoreCase ? strtolower($search) : $search;
        $text   = $ignoreCase ? strtolower($text) : $text;

        return $search === '' || (($temp = mb_strlen($text) - mb_strlen($search)) >= 0 && mb_strpos($text, $search, $temp) !== false);
    }

    public static function limit($text, $count = 20) {
        if (mb_strlen($text) > $count)
            $text = mb_substr($text, 0, $count) . '...';
        return $text;
    }

    public static function random($length, $viewingSuitable = false): string {
        $chars = $viewingSuitable
            ? 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz0123456789'//all letters minus (l,I)
            : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        return self::_random($length, $chars);
    }

    /**
     * @param int $length
     * @param string $chars
     * @return string
     */
    private static function _random(int $length, string $chars): string {
        $max = strlen($chars) - 1;
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            try {
                /** @noinspection PhpUndefinedFunctionInspection */
                $rnd = random_int(0, $max);
            } catch (Exception $e) {
                /** @noinspection RandomApiMigrationInspection */
                $rnd = mt_rand(0, $max);
            }
            $out .= $chars[$rnd];
        }
        return $out;
    }


    /**
     * ucfirst
     *
     * @param string $str required
     * @param string $encoding default manifest::default_encoding
     * @return  string
     */
    public static function ucfirst(string $str, string $encoding = manifest::default_encoding): string {
        return $encoding
            ? mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
            : ucfirst($str);
    }

    /**
     * lcfirst
     *
     * Does not strtoupper first
     *
     * @param string $str required
     * @param string $encoding default manifest::default_encoding
     * @return  string
     */
    public static function lcfirst(string $str, string $encoding = manifest::default_encoding): string {
        return $encoding
            ? mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
            : lcfirst($str);
    }

    public static function randomCryptography($length): string {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            /** @noinspection PhpUndefinedFunctionInspection */
            $string .= substr(bin2hex(random_bytes($size)), 0, $size);
        }

        return $string;
    }

    public static function equals($expression1, $expression2, $use_mb = false): bool {

        if ($use_mb)
            return mb_strtolower($expression1) === mb_strtolower($expression2);

        return strtolower($expression1) === strtolower($expression2);
    }
}
