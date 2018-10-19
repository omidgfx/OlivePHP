<?php namespace Olive\Routing;
use Olive\Http\req;

class login extends Controller{

    public function fnIndex($args = []) {
        $v = req::get('ref');
        var_dump($v);
    }
}
