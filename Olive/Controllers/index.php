<?php namespace Olive\Routing;

use Olive\Models\Test;
use \Olive\Support\Html\Html as h;

class index extends Controller {

    public function fnIndex($args = []) {
        self::requireModules(['Mysqli', 'Auth']);
//        Auth::prove('login');
        var_dump(Test::select());
//        Auth::logout();
//        var_dump(Auth::is());

//        var_dump(Auth::attempt('name', 'pass'));

//        $u = new URL('a?omid=aar');
//        $u->addQuery('pejman', 'aa');

//        var_dump($u,$u.'');

//        echo $a=http_build_query($u->query);


        self::requireModule('Html');
        ?>
        <?= h::a([], 'ss') ?>
        <?php

    }
}



