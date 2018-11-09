<?php namespace Olive\Routing;

class Route {
    public  $controller = null;
    public  $action     = 'Index';
    private $extras     = [];
    /**
     * @var string[]
     */
    public $arguments = [];

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
        if(!key_exists($key, $this->extras)) return $fallback;
        return $this->extras[$key];
    }
}
