<?php namespace App\Controllers;

use Olive\Routing\Controller;

class index extends Controller {

    public function fnIndex($args = []) {
        self::renderView('hello_world');
    }
}



