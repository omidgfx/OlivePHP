<?php namespace Olive\Util;

abstract class Digit {
    public static function surroundDigitsBy($string, $before = '**', $after = '**') {
        return preg_replace("~([0-9]+)~", "$before$1$after", $string);
    }

    /**
     * Prettify numbers with 2 numbers in decimal place, examples:
     * <br/>
     * 1. 1500          -> 1,500
     * 2. 1500.94511232 -> 1,500.94
     * <br/>
     * <br/>
     *
     * @param string|int $number
     * @param string $delimiter
     * @param int $decimal
     *
     * @return string
     */
    public static function prettify($number, $delimiter = ',', $decimal = 2) {
        $n = number_format($number, $decimal, '.', $delimiter);
        $l = strlen($n);
        if(substr($n, $l - 3) == '.00')
            $n = substr($n, 0, $l - 3);

        return $n;
    }

    /**
     * @param      $string
     * @param bool $prettify
     *
     * @return string
     */
    public static function en2fa($string, $prettify = FALSE) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $num     = range(0, 9);
        if($prettify)
            $string = self::prettify($string);

        return str_replace($num, $persian, $string);
    }

    /**
     * @param      $string
     * @param bool $prettify
     *
     * @return mixed
     */
    public static function fa2en($string, $prettify = FALSE) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $num     = range(0, 9);
        if($prettify)
            $string = self::prettify($string);

        return str_replace($persian, $num, $string);
    }

    /**
     * @param $n
     *
     * @return string
     */
    public static function sign($n) {
        if($n > 0)
            return "+$n";

        return $n;
    }

    /**
     * format big numbers to 1k, 2k, 15m etc.
     *
     * @param       $n
     * @param array $parts
     *
     * @return string
     */
    public static function thousands($n, $parts = ['k', 'm', 'b', 't']) {
        if($n > 1000) {
            $x               = round($n);
            $x_number_format = number_format($x);
            $x_array         = explode(',', $x_number_format);
            $x_parts         = $parts;
            $x_count_parts   = count($x_array) - 1;
            $x_display       = $x_array[0] . ((int)$x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
            $x_display       .= $x_parts[$x_count_parts - 1];

            return $x_display;
        } else
            return strval($n);
    }

    /**
     * @param        $size
     * @param array $units
     * @param string $sep
     *
     * @return string
     */
    public static function bytesFormat($size, $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB',], $sep = ' ') {
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $f     = number_format($size / pow(1024, $power), 2, '.', ',');
        $l     = strlen($f);
        if(substr($f, $l - 3) == '.00')
            $f = substr($f, 0, $l - 3);

        return "$f$sep$units[$power]";
    }
}
