<?php namespace Olive\Routing;

class RouteBypass {
    public $pattern;
    public $target;

    /**
     * RouteBypass constructor.
     * RoutBypass will put address in target and reform it via pattern and then pass it to the controller
     *
     * @param string $pattern
     * Use + for one argument from address and * for all arguments or rest of arguments after last + sign or
     * before first + sign
     *
     * <br>
     * <br>
     * <font color="#00c2ff">ex1</font>: if you created a pattern like <code>"mycontroller/*‌/+/*"</code> then you have
     * 3 arguments in in $target:
     *
     * 1. ($1) all arguments minus last 2
     *
     * 2. ($2) the argument before last one
     *
     * 3. ($3) last argument
     *
     * <br>
     * <br>
     * <font color="#00c2ff">ex2</font>: a pattern like <code>"mycontroller/+/*‌/+/+"</code> puts 4 arguments in
     * $pattern
     *
     * 1. ($1) the first ever argument
     *
     * 2. ($2) all arguments minus the first one and last 2 arguments
     *
     * 3. ($3) the argument before the last one
     *
     * 4. ($4) the last argument
     *
     * @param string $target
     */

    public function __construct($pattern, $target) {
        $this->pattern = $pattern;
        $this->target  = $target;
    }

    /**
     * @param string $route
     *
     * @return bool|mixed|string
     */
    public function matchAndBypass($route) {
        $pattern = preg_quote($this->pattern, '/');
        $pattern = str_replace('\+', '([^\/]*)', $pattern);
        $pattern = '/^' . str_replace('\*', '(.*)', $pattern) . '$/';
        $m       = [];
        if(preg_match($pattern, $route, $m)) {
            //Matches
            $route = $this->target;
            for($i = 1; $i < count($m); $i++)
                $route = str_replace("($$i)", $m[$i], $route);

            return $route;
        }

        return FALSE;
    }
}
