<?php namespace Olive\Http;

class URL
{

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

    /**
     * URL constructor.
     * @param string $url
     */
    public function __construct($url = null) {

        if ($url === null)
            return;

        # parse
        $u = parse_url($url);

        # fill this
        foreach ($u as $k => $v)
            $this->{$k} = (string)$v;

        # parse query
        if ($this->query) {
            parse_str($this->query, $q);
            $this->query = $q;
        }
    }

    /**
     * ## Parse given `$url`
     * * **Strings** _returns:_ `src($url)`
     * * **Escaped strings** (strings they starts with back-slash \\) _returns:_ `substr($url, 1)`
     * * **Array** [string,bool] with 2 elements _returns:_ `src(string, bool)`
     * @param string|array|self $array
     * @return URL
     */
    public static function parse($array) {
        $url  = $array;
        $full = false;
        if (is_array($array) && $array) {
            if (count($array) !== 1)
                $full = (bool)$array[1];
            $url = $array[0];
        } elseif ($array instanceof self)
            return $array;

        return self::make($url, true, $full);
    }

    /**
     * @param string $url
     * @param bool $relative
     * @param bool $full
     * @return URL
     */
    public static function make($url = null, $relative = true, $full = false) {
        $u           = new self($url);
        $u->relative = $relative;
        $u->full     = $full;
        return $u;
    }

    public function addQuery($name, $val) {
        if (!$this->query)
            $this->query = [];
        $this->query[$name] = $val;
        return $this;
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
     * @return URL
     */
    public function setScheme($scheme) {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * @param string $host
     * @return URL
     */
    public function setHost($host) {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param string $user
     * @return URL
     */
    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getPass() {
        return $this->pass;
    }

    /**
     * @param string $pass
     * @return URL
     */
    public function setPass($pass) {
        $this->pass = $pass;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param string $path
     * @return URL
     */
    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @param array $query
     * @return URL
     */
    public function setQuery($query) {
        $this->query = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getFragment() {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     * @return URL
     */
    public function setFragment($fragment) {
        $this->fragment = $fragment;
        return $this;
    }

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

    #endregion

    #region Option methods

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

    public function append($path) {
        if (empty($this->path))
            $this->path = $path;
        else
            $this->path .= '/' . ltrim($path, '/');
        return $this;
    }

    public function __toString() {

        $url = '';
        if ($this->scheme !== null) {
            $this->relative = false;
            $url            .= $this->scheme . '://';
        }

        if ($hostIsNotNull = ($this->host !== null)) {


            if ($userIsNotNull = ($this->user !== null))
                $url .= $this->user;

            if ($passIsNotNull = ($this->pass !== null))
                $url .= ":$this->pass";


            if ($userIsNotNull || $passIsNotNull)
                $url .= '@';


            $this->full = false;
            $url        .= $this->host;

        }

        $port = $this->getPort();
        if ($port > 0)
            $url .= ':' . $port;


        if ($this->path !== null) {
            if ($this->path[0] === '\\') {
                $this->relative = false;
                $url            .= substr($this->path, 1);
            } else
                $url .= '/' . ltrim($this->path, '/');
        }

        if ($this->query)
            $url .= '?' . http_build_query($this->query);

        return $this->relative ? url($url, $this->full) : $url;

    }

    /**
     * @return int
     */
    public function getPort() {
        return (int)$this->port;
    }
    #endregion

    #region Magic methods

    /**
     * @param int $port
     * @return URL
     */
    public function setPort($port) {
        $this->port = $port;
        return $this;
    }

    #endregion
}


