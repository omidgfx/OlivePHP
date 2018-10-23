<?php namespace Olive\Routing;

use Olive\Http\req;
use Olive\Models\Test;
use \Olive\Support\Html\Html as h;
use Olive\Util\Text;

class index extends Controller {

    public function fnIndex($args = []) {
        self::requireModules(['Mysqli', 'Auth']);
        $a = Text::randomByPattern('4-2-8');
        var_dump($a, strlen($a));
        echo '<pre>',req::report(['name'=>"پژمان"]),'</pre>';
        echo '<hr>';
        echo '<pre>',req::report($_SERVER),'</pre>';
        echo '<pre>',req::report(Test::select()),'</pre>';
//        var_dump(Text::randomByPattern('8-4-4-4-12', $a));

//        Auth::prove('login');
//        var_dump(Test::select());
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



