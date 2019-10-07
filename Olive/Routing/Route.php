<?php namespace Olive\Routing;

class Route
{
    public $controller;
    public $action = 'Index';
    /**
     * @var string[]
     */
    public  $arguments = [];
    private $extras    = [];

    /**
     * @param string $key
     * @param mixed $val
     */
    public function putExtra($key, $val) {
        $this->extras[$key] = $val;
    }

    /**
     * @param $key
     * @param null $fallback
     * @return mixed
     */
    public function getExtra($key, $fallback = null) {
        if (!array_key_exists($key, $this->extras)) return $fallback;
        return $this->extras[$key];
    }
}
