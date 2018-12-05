<?php namespace App\Controllers;
use App\Models\Test;
use Olive\Routing\Controller;

class index extends Controller {
    public function fnIndex($args = []) {
        var_dump(Test::select());
        self::renderView('hello_world');
    }
}



