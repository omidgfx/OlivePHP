<?php namespace Olive\Http;

class Header
{
    /** @var string */
    private $header;

    /** @var bool */
    private $replace;

    /**
     * Header constructor.
     * @param string $name
     * @param string $content
     * @param bool $replace
     */
    public function __construct(string $name, string $content, bool $replace = true) {
        $this->replace = $replace;
        $this->header  = "$name: $content";
    }

    /**
     * @param string $header
     * @param bool $replace
     * @return Header
     */
    public static function raw(string $header, bool $replace = true): Header {
        $instance         = new static(null, null, $replace);
        $instance->header = $header;
        return $instance;
    }

    /**
     * @param int|null $statusCode
     */
    public function execute(int $statusCode = null) {
        header((string)$this, $this->replace, $statusCode);
        unset($this);
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->header;
    }

}
