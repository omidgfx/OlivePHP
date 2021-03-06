<?php namespace Olive\Http;

abstract class req
{

    /**
     * IP address of client
     * @return string
     */
    public static function ip() {
        $client  = $_SERVER['HTTP_CLIENT_IP'] ?? null;
        $forward = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    /**
     * @param      $key
     * @param null $fallBack
     *
     * @return File
     */
    public static function file($key, $fallBack = null) {
        if (isset($_FILES[$key]))
            return new File($_FILES[$key]);

        return $fallBack;
    }

    /**
     * @param string|array $posts
     *
     * @return bool
     */
    public static function inPosts($posts) {
        if (is_array($posts)) {
            foreach ($posts as $str)
                if (!isset($_POST[$str]))
                    return false;
        } else return isset($_POST[$posts]);

        return true;
    }

    public static function inGets($gets) {
        if (is_array($gets)) {
            foreach ($gets as $str)
                if (!isset($_GET[$str]))
                    return false;
        } else return isset($_GET[$gets]);

        return true;
    }

    public static function posts($keys) {
        $r = [];
        foreach ($keys as $key) {
            $r[$key] = self::post($key);
        }

        return $r;
    }

    /**
     * Using $_POST
     *
     * @param string $key
     * @param mixed $fallback
     *
     * @return mixed
     */
    public static function post($key, $fallback = null) {
        if (isset($_POST[$key]) && $_POST[$key] !== '')
            return $_POST[$key];

        return $fallback;
    }

    public static function gets($keys) {
        $r = [];
        foreach ($keys as $key) {
            $r[$key] = self::get($key);
        }

        return $r;
    }

    /**
     * Using $_GET
     *
     * @param string $key
     * @param mixed $fallback
     *
     * @return mixed
     */
    public static function get($key, $fallback = null) {
        if (isset($_GET[$key]) && $_GET[$key] !== '')
            return $_GET[$key];

        return $fallback;
    }

    public static function requests($keys) {
        $r = [];
        foreach ($keys as $key) {
            $r[$key] = self::request($key);
        }

        return $r;
    }

    /**
     * Using $_REQUEST
     *
     * @param string $key
     * @param mixed $fallback
     *
     * @return mixed
     */
    public static function request($key, $fallback = null) {
        if (isset($_REQUEST[$key]) && $_REQUEST[$key] !== '')
            return $_REQUEST[$key];

        return $fallback;
    }

    public static function postInts(array $keys) {
        $r = [];
        foreach ($keys as $key) {
            $r[$key] = self::postInt($key);
        }

        return $r;
    }

    /**
     * @param     $key
     * @param int $fallback
     *
     * @return int
     */
    public static function postInt($key, $fallback = 0) {
        $g = self::post($key);
        if ($g === null) return $fallback;
        if (is_numeric($g)) return (int)$g;

        return $fallback;
    }

    public static function getInts(array $keys) {
        $r = [];
        foreach ($keys as $key) {
            $r[$key] = self::getInt($key);
        }

        return $r;
    }

    /**
     * @param     $key
     * @param int $fallback
     *
     * @return int
     */
    public static function getInt($key, $fallback = 0) {
        $g = self::get($key);
        if ($g === null) return $fallback;
        if (is_numeric($g)) return (int)$g;

        return $fallback;
    }

    public static function requestInts(array $keys) {
        $r = [];
        foreach ($keys as $key) {
            $r[$key] = self::requestInt($key);
        }

        return $r;
    }

    /**
     * @param     $key
     * @param int $fallback
     *
     * @return int
     */
    public static function requestInt($key, $fallback = 0) {
        $g = self::request($key);
        if ($g === null) return $fallback;
        if (is_numeric($g)) return (int)$g;

        return $fallback;
    }

    /**
     * <p>If set $desire for wanted type, method will return a boolean, otherwise method returns
     * $_SERVER['REQUEST_METHOD']
     *
     * @param $desire
     *
     * @return bool|string
     */
    public static function method($desire = null) {
        if ($desire) {
            return strtolower($_SERVER['REQUEST_METHOD']) === strtolower($desire);
        } else
            return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param array $extras
     * @param string $level 'post'|'get'|null=all
     * @return string
     */
    public static function report($extras = null, $level = null) {

        $dec = static function ($var) {
            $out = [];
            foreach ($var as $k => $v)
                $out[$k] = urldecode($v);
            return $out;
        };

        $vars['URL'] = rtrim($_SERVER['HTTP_HOST'], '/') . '/' . ltrim($_SERVER['REQUEST_URI'], '/');

        switch ($level) {
            case 'get':
                $vars = array_merge($vars, ['Get' => $dec($_GET)]);
                break;
            case 'post':
                $vars = array_merge($vars, ['Post' => $_POST]);
                break;
            default:
                $vars = array_merge($vars, ['Post' => $_POST, 'Get' => $dec($_GET)]);
                break;
        }

        # EXTRAS
        if ($extras !== null)
            $vars['Extras'] = $extras;

        return json_encode($vars, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    }

    /**
     * @param null $fallback
     * @return null
     */
    public static function referer($fallback = null) {
        return $_SERVER['HTTP_REFERER'] ?? $fallback;
    }
}
