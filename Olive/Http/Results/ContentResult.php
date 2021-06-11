<?php namespace Olive\Http\Results;


use Olive\Http\Header;

class ContentResult extends ActionResult
{

    /** @var string */
    private string $content;

    /** @var string */
    private string $contentEncoding;

    /** @var string */
    private string $contentType;

    /**
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): static {
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
    public function setContentEncoding(string $contentEncoding): static {
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
    public function setContentType(string $contentType): static {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType(): string {
        return $this->contentType;
    }

    public function executeResult(): void {

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
