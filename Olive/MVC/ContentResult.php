<?php namespace Olive\MVC;


use Olive\Http\Header;

class ContentResult extends ActionResult
{

    /** @var string */
    private $content;

    /** @var string */
    private $contentEncoding;

    /** @var string */
    private $contentType;

    /**
     * @param string $content
     * @return $this
     */
    public function setContent(string $content) {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * @param string $contentEncoding
     * @return $this
     */
    public function setContentEncoding(string $contentEncoding) {
        $this->contentEncoding = $contentEncoding;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentEncoding(): string {
        return $this->contentEncoding;
    }

    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType) {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType(): string {
        return $this->contentType;
    }

    public function executeResult(Controller $controller) {

        $headers = $this->getHeaders();

        if ($this->contentType)
            $headers[] = new Header('Content-Type', $this->contentType);

        if ($this->contentEncoding)
            $headers[] = new Header('Content-Encoding', $this->contentEncoding);

        # send headers
        $this->executeHeaders($headers);

        echo $this->content;
    }
}
