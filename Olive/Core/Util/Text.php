<?php namespace Olive\Util;

abstract class Text {
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

        return implode("\n", $firsts) . ((count($lines) > $num && $ellip != NULL) ? $ellip : NULL);
    }

    /**
     * @param string|string[] $search
     * @param $text
     *
     * @param bool $ignoreCase
     *
     * @return bool
     */
    public static function startsWith($search, $text, $ignoreCase = FALSE) {

        if(is_array($search)) {
            foreach($search as $d)
                if(self::startsWith($d, $text, $ignoreCase)) return TRUE;

            return FALSE;
        }
        $search = $ignoreCase ? strtolower($search) : $search;
        $text   = $ignoreCase ? strtolower($text) : $text;

        return $search === "" || mb_strrpos($text, $search, -mb_strlen($text)) !== FALSE;

    }

    /**
     * @param string|string[] $search
     * @param $text
     *
     * @param bool $ignoreCase
     *
     * @return bool
     */
    public static function endsWith($search, $text, $ignoreCase = FALSE) {
        if(is_array($search)) {
            foreach($search as $d)
                if(self::endsWith($d, $text, $ignoreCase)) return TRUE;

            return FALSE;
        }


        $search = $ignoreCase ? strtolower($search) : $search;
        $text   = $ignoreCase ? strtolower($text) : $text;

        return $search === "" || (($temp = mb_strlen($text) - mb_strlen($search)) >= 0 && mb_strpos($text, $search, $temp) !== FALSE);
    }

    public static function limit($text, $count = 20) {
        if(mb_strlen($text) > $count)
            $text = mb_substr($text, 0, $count) . '...';
        return $text;
    }

    public static function random($length, $viewingSuitable = FALSE) {

        $chars = $viewingSuitable
            ? 'ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz0123456789'//all letters minus (l,I)
            : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $max = strlen($chars) - 1;
        $out = '';
        for($i = 0; $i < $length; $i++)
            $out .= $chars[mt_rand(0, $max)];

        return $out;
    }
}
