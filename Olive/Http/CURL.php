<?php namespace Olive\Http;

class CURL {

    /** @var resource */
    private $resource;
    private $result;
    private $headers = [];

    /**
     * CURL constructor.
     * @param URL|string $url
     * @param $options
     */
    public function __construct($url, $options = []) {

        # appliciate url
        if($url instanceof URL)
            $url = (string)$url;

        # init
        $this->resource = curl_init($url);
        foreach($options as $key => $value)
            $this->setOption($key, $value);

        # set return transfer
        $this->setReturnTransfer(true);

    }

    /**
     * @param array $headers
     * @return CURL
     */
    public function setHeaders($headers) {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @param $name
     * @param $val
     * @return CURL
     */
    public function addHeader($name, $val) {
        $this->headers[] = "$name: $val";
        return $this;
    }

    /**
     * @param bool $state
     * @return CURL
     */
    public function setReturnTransfer($state = true) {
        return $this->setOption(CURLOPT_RETURNTRANSFER, $state);
    }

    /**
     * @param bool $state
     * @return CURL
     */
    public function setSSLVerifyPeer($state = false) {
        return $this->setOption(CURLOPT_SSL_VERIFYPEER, $state);
    }

    /**
     * @param int $state
     * @return CURL
     */
    public function setSSLVerifyHost($state = 0) {
        return $this->setOption(CURLOPT_SSL_VERIFYHOST, $state);
    }

    /**
     * @param string $method
     * @return CURL
     */
    public function setCustomRequest($method = 'POST') {
        return $this->setOption(CURLOPT_CUSTOMREQUEST, $method);
    }

    /**
     * @param array|string $fields
     * @param bool $asQuery
     * @return CURL
     */
    public function setPostFields($fields, $asQuery = false) {
        return $this->setOption(CURLOPT_POSTFIELDS, $asQuery ? http_build_query($fields) : $fields);
    }

    /**
     * @param int $key
     * @param $value
     * @return $this
     */
    public function setOption($key, $value) {
        curl_setopt($this->resource, $key, $value);
        return $this;
    }

    /**
     * @param bool $close
     * @return $this
     */
    public function exec($close = true) {

        if($this->headers)
            $this->setOption(CURLOPT_HTTPHEADER, $this->headers);

        $this->result = curl_exec($this->resource);
        if($close) curl_close($this->resource);
        return $this;
    }

    public function getResult() {
        return $this->result;
    }

    /**
     * (PHP 5 &gt;= 5.2.0, PECL json &gt;= 1.2.0)<br/>
     * Decodes a JSON string
     * @link https://php.net/manual/en/function.json-decode.php
     * @param bool $assoc [optional] <p>
     * When <b>TRUE</b>, returned objects will be converted into
     * associative arrays.
     * </p>
     * @param int $depth [optional] <p>
     * User specified recursion depth.
     * </p>
     * @param int $options [optional] <p>
     * Bitmask of JSON decode options. Currently only
     * <b>JSON_BIGINT_AS_STRING</b>
     * is supported (default is to cast large integers as floats)
     *
     * <b>JSON_THROW_ON_ERROR</b> when passed this flag, the error behaviour of these functions is changed. The global error state is left untouched, and if an error occurs that would otherwise set it, these functions instead throw a JsonException
     * </p>
     * @return mixed the value encoded in <i>json</i> in appropriate
     * PHP type. Values true, false and
     * null (case-insensitive) are returned as <b>TRUE</b>, <b>FALSE</b>
     * and <b>NULL</b> respectively. <b>NULL</b> is returned if the
     * <i>json</i> cannot be decoded or if the encoded
     * data is deeper than the recursion limit.
     */
    public function jsonDecodeResult($assoc = true, $depth = 512, $options = 0) {
        return json_decode($this->getResult(), $assoc, $depth, $options);
    }

}

