<?php namespace Olive\Exceptions;

use Throwable;

class MethodNotAllowedException extends HttpException
{
    protected int   $httpResponseCode = 405;
    protected array $allowedMethods;

    /**
     * @return array
     */
    public function getAllowedMethods(): array {
        return $this->allowedMethods;
    }

    /**
     * @param array $allowedMethods
     * @return MethodNotAllowedException
     */
    public function setAllowedMethods(array $allowedMethods): MethodNotAllowedException {
        $this->allowedMethods = $allowedMethods;
        return $this;
    }


    public static function make($allowedMethods = [], $message = '', $code = 0, Throwable $previous = null): static {
        $e = new static($message, $code, $previous);
        return $e->setAllowedMethods($allowedMethods);
    }
}