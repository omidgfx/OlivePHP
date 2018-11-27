<?php namespace Olive\Http;

use Olive\Exceptions\URLException;

class URL {

    #region Parts
    private $scheme;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $path;
    private $query;
    private $fragment;
    #endregion

    #region Options
    private $relative = true;
    private $full     = false;

    #endregion

    public function __construct($url = null) {

        if($url == null)
            return;

        # parse
        $u = parse_url($url);

        if($u === false)
            throw new URLException('Invalid url');

        # fill this
        foreach($u as $k => $v)
            $this->{$k} = strval($v);

        # parse query
        if($this->query) {
            parse_str($this->query, $q);
            $this->query = $q;
        }
    }

    public function addQuery($name, $val) {
        if(!$this->query)
            $this->query = [];
        $this->query[$name] = $val;
        return $this;
    }

    /**
     * @param string $url
     * @param bool $relative
     * @param bool $full
     * @return URL
     * @throws URLException
     */
    public static function make($url = null, $relative = true, $full = false) {
        $u           = new self($url);
        $u->relative = $relative;
        $u->full     = $full;
        return $u;
    }

    /**
     * ## Parse given `$url`
     * * **Strings** _returns:_ `src($url)`
     * * **Escaped strings** (strings they starts with back-slash \\) _returns:_ `substr($url, 1)`
     * * **Array** [string,bool] with 2 elements _returns:_ `src(string, bool)`
     * @param string|array|self $array
     * @return URL
     * @throws \Olive\Exceptions\URLException
     */
    public static function parse($array) {
        $url  = $array;
        $full = false;
        if(is_array($array) and $array) {
            if(count($array) != 1)
                $full = !!$array[1];
            $url = $array[0];
        } elseif($array instanceof self)
            return $array;

        $u = self::make($url, true, $full);
        return $u;
    }

    #region Setter and getters


    /**
     * @return string
     */
    public function getScheme() {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     */
    public function setScheme($scheme) {
        $this->scheme = $scheme;
    }

    /**
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort() {
        return intval($this->port);
    }

    /**
     * @param int $port
     */
    public function setPort($port) {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPass() {
        return $this->pass;
    }

    /**
     * @param string $pass
     */
    public function setPass($pass) {
        $this->pass = $pass;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQuery($query) {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getFragment() {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     */
    public function setFragment($fragment) {
        $this->fragment = $fragment;
    }

    #endregion

    #region Option methods

    /**
     * @return bool
     */
    public function isRelative() {
        return $this->relative;
    }

    /**
     * @param bool $relative
     */
    public function setRelative($relative) {
        $this->relative = $relative;
    }

    /**
     * @return bool
     */
    public function isFull() {
        return $this->full;
    }

    /**
     * @param bool $full
     */
    public function setFull($full) {
        $this->full = $full;
    }

    #endregion

    #region Magic methods

    public function __toString() {

        $url = '';
        if(!is_null($this->scheme)) {
            $this->relative = false;
            $url            .= $this->scheme . '://';
        }

        if($hostIsNotNull = !is_null($this->host)) {


            if($userIsNotNull = !is_null($this->user))
                $url .= $this->user;

            if($passIsNotNull = !is_null($this->pass))
                $url .= ":$this->pass";


            if($userIsNotNull || $passIsNotNull)
                $url .= '@';


            $this->full = false;
            $url        .= $this->host;

        }

        $port = $this->getPort();
        if($port > 0)
            $url .= ':' . $port;


        if(!is_null($this->path)) {
//            if($url != '') $url .= '/';

            if($this->path[0] == '\\') {
                $this->relative = false;
                $url            .= substr($this->path, 1);
            } else
                $url .= '/' . ltrim($this->path, '/');
        }

        if($this->query != [])
            $url .= '?' . http_build_query($this->query);

        return $this->relative ? src($url, $this->full) : $url;

    }

    #endregion
}


