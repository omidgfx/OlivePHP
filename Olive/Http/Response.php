<?php namespace Olive\Http;

use stdClass;

abstract class Response extends stdClass
{

    /** @var Header[] */
    private array $headers = [];

    /** @var int */
    private int $statusCode;

    /** @var bool */
    private bool $securityHeaders = false;

    /**
     * @param string $name
     * @param string $content
     * @param bool $replace
     * @return $this
     */
    public function addHeader(string $name, string $content, bool $replace = true): static {
        $this->headers[] = new Header($name, $content, $replace);
        return $this;
    }

    /**
     * @param Header[] $headers
     * @return $this
     */
    public function setHeaders(array $headers): static {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array {
        return $this->headers;
    }

    /**
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode(int $statusCode): static {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param int $fallback
     * @return int
     */
    public function getStatusCode(int $fallback = 200): int {
        return $this->statusCode ?? $fallback;
    }

    /**
     * @param Header[] $headers
     */
    protected function executeHeaders(array $headers): void {

        # security
        if ($this->securityHeaders)
            $headers = array_merge($this->securityHeaders(), $headers);

        # copyright
        $headers[] = new Header('X-Powered-By', 'OlivePHP 5.0.0 https://github.com/omidgfx/OlivePHP, PHP ' . PHP_VERSION);

        # response code
        self::sendHttpCode($this->getStatusCode());


        # execute
        array_map(static fn($header) => $header->execute(), $headers);
    }

    /**
     * @return Header[]
     */
    protected function securityHeaders(): array {
        return [
            new Header('X-Content-Type-Options', 'nosniff'),
            new Header('X-XSS-Protection', '1; mode=block'),
        ];
    }

    public function enableSecurityHeaders($enabled = true): static {
        $this->securityHeaders = $enabled;
        return $this;
    }

    #region Helpers

    /**
     * @param int $response_code look at (<a href="https://en.wikipedia.org/wiki/List_of_HTTP_status_codes" target="_blank">List_of_HTTP_status_codes</a>) for more information.
     */
    public static function sendHttpCode(int $response_code): void {
        http_response_code($response_code);
    }

    #endregion
}