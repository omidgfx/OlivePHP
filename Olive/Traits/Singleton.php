<?php namespace Olive\Traits;

trait Singleton
{
    /**
     * Store the singleton object.
     */
    private static $singleton;

    /**
     * Create an inaccessible contructor.
     * singleton constructor.
     */
    public function __construct() {
        $this->_singletonConstruct();
    }

    abstract protected function _singletonConstruct();

    /**
     * Fetch an instance of the class
     * @return self
     */
    public static function getInstance() {
        if (self::$singleton === null)
            self::$singleton = new self;
        return self::$singleton;
    }
}
