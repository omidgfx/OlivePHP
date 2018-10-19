<?php namespace Olive\Support\Auth;

use Olive\Auth\Authenticatable;

class AuthResult {
    const INVALID_PASSWORD   = -1;
    const INVALID_IDENTIFIER = -2;
    const SUCCESS            = 1;

    /** @var int */
    private $result;
    /** @var Authenticatable */
    public $authenticatable;

    /**
     * AuthResult constructor.
     * @param int $result
     * @param Authenticatable $authenticatable
     */
    public function __construct($result, $authenticatable = NULL) {
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