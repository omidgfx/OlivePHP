<?php namespace Olive\Exceptions;

use Throwable;

class BadRequestException extends HttpException
{
    protected int   $httpResponseCode = 400;
    protected array $inputs;

    /**
     * @return array
     */
    public function getInputs(): array {
        return $this->inputs;
    }

    /**
     * @param array $inputs
     * @return self
     */
    public function setInputs(array $inputs): self {
        $this->inputs = $inputs;
        return $this;
    }

    public static function make(array $inputs, $message = '', $code = 0, Throwable $previous = null): static {
        $e = new static($message, $code, $previous);
        return $e->setInputs($inputs);
    }

}