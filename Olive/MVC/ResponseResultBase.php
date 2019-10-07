<?php namespace Olive\MVC;

use Olive\Http\Header;
use stdClass;

abstract class ResponseResultBase extends stdClass
{
    /** @var Header[] */
    private $headers = [];

    /** @var int */
    private $statusCode;

    /** @var bool */
    private $securityHeaders = true;

    /**
     * @param string $name
     * @param string $content
     * @param bool $replace
     * @return $this
     */
    public function addHeader(string $name, string $content, bool $replace = true) {
        $this->headers[] = new Header($name, $content, $replace);
        return $this;
    }

    /**
     * @param Header[] $headers
     * @return $this
     */
    public function setHeaders(array $headers) {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return Header[]
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode(int $statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * @param Header[] $headers
     */
    protected function executeHeaders(array $headers): void {
        if ($this->securityHeaders)

            foreach ($headers as $header)
                $header->execute();
    }

    /**
     * @return Header[]
     */
    protected function securityHeaders() {
        return [
            new Header('X-Content-Type-Options', 'nosniff'),
            new Header('X-XSS-Protection', '1; mode=block'),
        ];
    }

    public function enableSecurityHeaders($enabled = true) {
        $this->securityHeaders = $enabled;
    }
}
