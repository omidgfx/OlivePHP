<?php namespace Olive;

trait Singleton {
    /**
     * Store the singleton object.
     */
    private static $singleton = false;

    /**
     * Create an inaccessible contructor.
     * singleton constructor.
     */
    public function __construct() {
        $this->__instance();
    }


    /**
     * Fetch an instance of the class
     * @return self
     */
    public static function getInstance() {
        if(self::$singleton === false)
            self::$singleton = new self;
        return self::$singleton;
    }

    protected abstract function __instance();
}