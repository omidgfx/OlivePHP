<?php namespace Olive\Util;

use Exception;

abstract class Text
{
    /**
     * Limit given text to numbers of line
     *
     * @param        $str
     * @param int $num
     * @param string $ellip
     *
     * @return string
     */
    public static function limitLines($str, $num = 5, $ellip = '…') {
        $lines  = explode("\n", $str);
        $firsts = array_slice($lines, 0, $num);

        return implode("\n", $firsts) . ((count($lines) > $num && $ellip !== null) ? $ellip : null);
    }

    /**
     * @param string|string[] $search
     * @param $text
     *
     * @param bool $ignoreCase
     *
     * @return bool
     */
    public static function startsWith($search, $text, $ignoreCase = false) {

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
    public static function endsWith($search, $text, $ignoreCase = false) {
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

    public static function random($length, $viewingSuitable = false) {
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
    private static function _random($length, $chars) {
        $max = strlen($chars) - 1;
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            try {
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
     * @param string $pattern '8-4-4-4-12'
     * @return string
     */
    public static function randomByPattern($pattern) {
        return preg_replace_callback("(\d+)", static function ($match) {
            return self::_random($match[0], 'abcdefghijklmnopqrstuvwxyz1234567890');
        }, $pattern);
    }

    /**
     * @param string|string[] $pattern '8-4-4-4-12'
     * @param string $text
     * @return bool
     */
    public static function validateByPattern($pattern, $text) {
        if (!is_array($pattern))
            $pattern = [$pattern];
        foreach ($pattern as $p) {

            # validation
            $rule = preg_replace_callback('(\d+)', static function ($match) {
                return '\w{' . $match[0] . '}';
            }, '/^' . $p . '$/');

            #check

            preg_match($rule, $text, $matches);
            if ($matches !== [])
                return true;
        }
        return false;
    }

    /**
     * ucfirst
     *
     * @param string $str required
     * @param string $encoding default UTF-8
     * @return  string
     */
    public static function ucfirst($str, $encoding = 'UTF-8') {
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
     * @param string $encoding default UTF-8
     * @return  string
     */
    public static function lcfirst($str, $encoding = 'UTF-8') {
        return $encoding
            ? mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
            : lcfirst($str);
    }

    public static function randomCryptography($length) {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            /** @noinspection PhpUnhandledExceptionInspection */
            $string .= substr(bin2hex(random_bytes($size)), 0, $size);
        }

        return $string;
    }
}
