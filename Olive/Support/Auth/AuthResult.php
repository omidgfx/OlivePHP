<?php namespace Olive\Support\Auth;

class AuthResult
{
    public const INVALID_PASSWORD   = -1;
    public const INVALID_IDENTIFIER = -2;
    public const SUCCESS            = 1;
    /** @var Authenticatable */
    public $authenticatable;
    /** @var int */
    private $result;

    /**
     * AuthResult constructor.
     * @param int $result
     * @param Authenticatable $authenticatable
     */
    public function __construct($result, $authenticatable = null) {
        $this->result          = $result;
        $this->authenticatable = $authenticatable;
    }

    public function isSucceed() {
        return $this->result === static::SUCCESS;
    }

    public function isInvalidPassword() {
        return $this->result === static::INVALID_PASSWORD;
    }

    public function isInvalidIdentifier() {
        return $this->result === static::INVALID_IDENTIFIER;
    }

    /**
     * @return int
     */
    public function getResult() {
        return $this->result;
    }

}
