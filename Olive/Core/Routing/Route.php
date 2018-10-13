<?php namespace Olive\Routing;

class Route {
    public  $controller = NULL;
    public  $action     = CONTROLLER_INDEX;
    public  $url;
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
    public function getExtra($key, $fallback = NULL) {
        if(!key_exists($key, $this->extras)) return $fallback;
        return $this->extras[$key];
    }
}
