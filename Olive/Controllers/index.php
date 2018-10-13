<?php namespace Olive\Routing;

use Olive\Html\Form as form;
use Olive\Html\Html;
use Olive\Security\CSRFTokenizer\CSRFToken;
use Olive\Util\Text;

class index extends Controller {

    public function fnIndex($args = []) {
        self::requireModule('html');
        $time = microtime(TRUE);
        for($i = 0; $i < 1000; $i++)
            Text::random(32);
        $time2 = microtime(TRUE);
        echo '<pre>', Html::entitiesEncode($time . ' - ' . $time2 . ' = ' . ($time2 - $time)), '</pre>';

        $token = CSRFToken::generate('')
        ?>
        <html>
        <body>
        <?php

        echo form::open('\\#');
        echo form::a('\\#', 'my title');
        echo form::close();

        ?>
        </body>
        </html>
        <?php
    }

}


