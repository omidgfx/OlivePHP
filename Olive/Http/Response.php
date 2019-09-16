<?php namespace Olive\Http;

abstract class Response
{

    //region Helers

    /**
     * @param string $target_url
     */
    public static function redirect($target_url) {
        self::setHeader('Location', $target_url);
        die;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @param null|int $response_code look at (<a href="https://en.wikipedia.org/wiki/List_of_HTTP_status_codes" target="_blank">List_of_HTTP_status_codes</a>) for more information.
     */
    public static function setHeader($name, $value, $replace = true, $response_code = null) {
        if ($value)
            $s = "$name: $value";
        else
            $s = $name;
        header($s, $replace, $response_code);
    }

    /**
     * @param int $response_code look at (<a href="https://en.wikipedia.org/wiki/List_of_HTTP_status_codes" target="_blank">List_of_HTTP_status_codes</a>) for more information.
     */
    public static function setHttpCode($response_code) {
        http_response_code($response_code);
    }

    //endregion

    //region Dumpers

    /**
     * @param mixed $content
     * @param bool $alive
     * @param bool $preventCache
     */
    public static function jsonDump($content, $alive = false, $preventCache = false) {
        $json = json_encode($content, JSON_UNESCAPED_UNICODE);
        if (!$preventCache) {
            header("Pragma: no-cache");
            header("Cache-Control: no-cache, must-revalidate");
        }
        if ($alive)
            header("Keep-Alive: timeout=5, max=100");
        header("Content-Type: application/json");
        header("Content-Length: " . strlen($json));
        echo $json;
        die;
    }

    //endregion

}