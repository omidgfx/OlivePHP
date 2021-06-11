<?php namespace Olive\Http\Results;

use Olive\Exceptions\ActionResultException;
use Olive\Http\Header;

class JsonResult extends ActionResult
{


    /** @var mixed */
    private mixed $value;

    /** @var string */
    private string $contentType;

    /** @var string */
    private string $contentEncoding;

    /** @var int|null|false */
    private int|null|false $contentLength;

    /** @var int */
    private int $options;

    /** @var int */
    private int $depth;

    /**
     * JsonResult constructor.
     * @param object|array $value
     * @param int $options
     * @param int $depth
     */
    public function __construct(object|array $value, int $options = 0, int $depth = JSON_PARTIAL_OUTPUT_ON_ERROR) {
        $this->value   = $value;
        $this->options = $options;
        $this->depth   = $depth;
    }

    /**
     * @param mixed $value
     * @return JsonResult
     */
    public function setValue(mixed $value): static {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed {
        return $this->value;
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

    /**
     * @param int $options
     * @return JsonResult
     */
    public function setOptions(int $options): static {
        $this->options = $options;
        return $this;
    }

    /**
     * @param int $depth
     * @return JsonResult
     */
    public function setDepth(int $depth): static {
        $this->depth = $depth;
        return $this;
    }

    /**
     * @param bool|int|null $contentLength
     * @return JsonResult
     */
    public function setContentLength(bool|int|null $contentLength): static {
        $this->contentLength = $contentLength;
        return $this;
    }

    /**
     * @throws ActionResultException
     */
    public function executeResult(): void {

        /** @var string|false $content */
        $content = json_encode($this->value, $this->options, $this->depth);

        if ($content === false)
            throw new ActionResultException('Could not encode JSON');

        $headers = $this->getHeaders();

        $headers[] = new Header('Content-Type', empty($this->contentType) ? 'application/json' : $this->contentType);

        if ($this->contentLength === null)
            $headers[] = new Header('Content-Length', strlen($content));
        elseif (is_numeric($this->contentLength))
            $headers[] = new Header('Content-Length', $this->contentLength);

        if ($this->contentEncoding)
            $headers[] = new Header('Content-Encoding', $this->contentEncoding);


        $this->executeHeaders($headers);

        echo $content;
    }
}
