<?php namespace Olive\Util;

class WithObject {
    /** @var object */
    protected $object;

    /**
     * WithObject constructor.
     * @param object $object
     */
    public function __construct($object) { $this->object = $object; }

    /**
     * @param string $key
     * @param mixed|callable $value
     * @param bool $run_callable
     * @return $this
     */
    public function set(string $key, $value, $run_callable = TRUE) {

        $this->object{$key} = is_callable($value) && $run_callable
            ? $value($this->object)
            : $value;

        return $this;
    }
}