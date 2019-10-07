<?php namespace Olive\MVC;

use Olive\Exceptions\ActionResultException;
use Olive\Http\Header;

class JsonResult extends ActionResult
{


    /** @var mixed */
    private $value;

    /** @var string */
    private $contentType;

    /** @var string */
    private $contentEncoding;

    /** @var int|null|false */
    private $contentLength;

    /** @var int */
    private $options;

    /** @var int */
    private $depth;

    /**
     * JsonResult constructor.
     * @param array|object $value
     * @param int $options
     * @param int $depth
     */
    public function __construct($value, int $options = 0, int $depth = JSON_PARTIAL_OUTPUT_ON_ERROR) {
        $this->value   = $value;
        $this->options = $options;
        $this->depth   = $depth;
    }

    /**
     * @param mixed $value
     * @return JsonResult
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
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

    /**
     * @param int $options
     * @return JsonResult
     */
    public function setOptions(int $options) {
        $this->options = $options;
        return $this;
    }

    /**
     * @param int $depth
     * @return JsonResult
     */
    public function setDepth(int $depth) {
        $this->depth = $depth;
        return $this;
    }

    /**
     * @param false|int|null $contentLength
     * @return JsonResult
     */
    public function setContentLength($contentLength) {
        $this->contentLength = $contentLength;
        return $this;
    }

    /**
     * @param Controller $controller
     * @throws ActionResultException
     */
    public function executeResult(Controller $controller) {

        $output = json_encode($this->value, $this->options, $this->depth);

        if ($output === false)
            throw new ActionResultException('Could not encode JSON');

        $headers = $this->getHeaders();

        $headers[] = new Header('Content-Type',
            $this->contentType === '' || $this->contentType === null
                ? 'application/json'
                : $this->contentType
        );

        if ($this->contentLength === null)
            $headers[] = new Header('Content-Length', strlen($this->contentEncoding));

        if (is_numeric($this->contentLength))
            $headers[] = new Header('Content-Length', $this->contentLength);

        if ($this->contentEncoding)
            $headers[] = new Header('Content-Encoding', $this->contentEncoding);


        $this->executeHeaders($headers);

        echo $output;
    }
}
