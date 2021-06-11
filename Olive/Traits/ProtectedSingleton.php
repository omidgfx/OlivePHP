<?php namespace Olive\Traits;

trait ProtectedSingleton
{
    private static $__singleton;

    /**
     * Create an inaccessible constructor.
     * singleton constructor.
     */
    public function __construct() {
        $this->_singletonConstruct();
    }

    abstract protected function _singletonConstruct(): void;

    /**
     * Fetch an instance of the class
     * @return self
     */
    protected static function getInstance(): static {
        if (self::$__singleton === null)
            self::$__singleton = new self;
        return self::$__singleton;
    }
}
