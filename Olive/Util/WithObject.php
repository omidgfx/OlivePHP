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
    public function set(string $key, $value, $run_callable = true) {

        $this->object->{$key} = is_callable($value) && $run_callable
            ? $value($this->object)
            : $value;

        return $this;
    }

    /**
     * @param array $assoc
     * @param bool $run_all_callables
     * @return $this
     */
    public function setArray(array $assoc, $run_all_callables = true) {
        foreach($assoc as $key => $value)
            $this->set($key, $value, $run_all_callables);
        return $this;
    }
}