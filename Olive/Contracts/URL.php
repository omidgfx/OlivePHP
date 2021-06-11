<?php namespace Olive\Contracts;

class URL
{

    #region Parts
    private null|string $scheme   = null;
    private null|string $host     = null;
    private null|int    $port     = null;
    private null|string $user     = null;
    private null|string $pass     = null;
    private null|string $path     = null;
    private null|array  $query    = [];
    private null|string $fragment = null;
    #endregion

    #region Setters & Getters
    /**
     * @return string|null
     */
    public function getScheme(): ?string {
        return $this->scheme;
    }

    /**
     * @param string|null $scheme
     * @return URL
     */
    public function setScheme(?string $scheme): URL {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string {
        return $this->host;
    }

    /**
     * @param string|null $host
     * @return URL
     */
    public function setHost(?string $host): URL {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int {
        return $this->port;
    }

    /**
     * @param int|null $port
     * @return URL
     */
    public function setPort(?int $port): URL {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string {
        return $this->user;
    }

    /**
     * @param string|null $user
     * @return URL
     */
    public function setUser(?string $user): URL {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPass(): ?string {
        return $this->pass;
    }

    /**
     * @param string|null $pass
     * @return URL
     */
    public function setPass(?string $pass): URL {
        $this->pass = $pass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string {
        return $this->path;
    }

    /**
     * @param string|null $path
     * @return URL
     */
    public function setPath(?string $path): URL {
        $this->path = $path;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getQuery(): ?array {
        return $this->query;
    }

    /**
     * @param array|null $query
     * @return URL
     */
    public function setQuery(?array $query): URL {
        $this->query = $query;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFragment(): ?string {
        return $this->fragment;
    }

    /**
     * @param string|null $fragment
     * @return URL
     */
    public function setFragment(?string $fragment): URL {
        $this->fragment = $fragment;
        return $this;
    }

    #endregion

    #region Constructor
    /**
     * URL constructor.
     * @param string|null $url
     */
    public function __construct(string $url = null) {

        if ($url === null)
            return;

        # parse
        $u = parse_url($url);

        # fill this
        foreach ($u as $k => $v)
            $this->{$k} = (string)$v;

        $this->scheme   = $u['scheme'] ?? null;
        $this->host     = $u['host'] ?? null;
        $this->port     = $u['port'] ?? null;
        $this->user     = $u['user'] ?? null;
        $this->pass     = $u['pass'] ?? null;
        $this->path     = $u['path'] ?? null;
        $this->fragment = $u['fragment'] ?? null;

        # parse query
        parse_str($u['query'] ?? '', $this->query);

    }
    #endregion

    #region Extenders & Magics
    public function addQuery($name, $val): static {
        if (!$this->query)
            $this->query = [];
        $this->query[$name] = $val;
        return $this;
    }

    public function append($path): static {
        if (empty($this->path))
            $this->path = $path;
        else
            $this->path .= '/' . ltrim($path, '/');
        return $this;
    }

    public function __toString() {

        $url = '';
        if ($this->scheme !== null) {
            $url .= $this->scheme . '://';
        }

        if ($this->host !== null) {


            if ($userIsNotNull = ($this->user !== null))
                $url .= $this->user;

            if ($passIsNotNull = ($this->pass !== null))
                $url .= ":$this->pass";


            if ($userIsNotNull || $passIsNotNull)
                $url .= '@';


            $url .= $this->host;

        }

        $port = $this->getPort();
        if ($port > 0)
            $url .= ':' . $port;


        if ($this->path !== null) {
            if ($this->path[0] === '\\') {
                $url .= substr($this->path, 1);
            } else
                $url .= '/' . ltrim($this->path, '/');
        }

        if ($this->query)
            $url .= '?' . http_build_query($this->query);

        return $url;

    }

    #endregion

    #region statics

    public static function toLink() {

    }
    #endregion

}


