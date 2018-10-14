<?php namespace Olive\Routing;

use Olive\Security\CSRFToken;
use Olive\Support\Html\Form as form;
use Olive\Util\DateTime;


class index extends Controller {

    public function fnIndex($args = []) {
        self::requireModule('html');
        $csrf = CSRFToken::generate();
        $dt = new DateTime;
        ?>
        <html>
        <body>
        <?php
        echo form::open('\\#');
        echo form::a('\\#', 'my title');
        echo form::token($csrf);
        echo form::nbsp(5);
        echo form::select('', [
            ''  => 'cc',
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => [
                'ea' => 'EA',
                'eb' => 'EB',
                'ec' => 'EC',
            ],
        ], 'ec', [], ['' => ['disabled' => 'disabled']]);

        echo form::radio('a',NULL,TRUE);
        echo form::checkbox('a',NULL,TRUE);
        echo form::color('a');
        echo form::radio('s', $dt);
        echo form::close();
        ?>
        </body>
        </html>
        <?php
    }

}


