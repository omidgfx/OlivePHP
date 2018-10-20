<?php namespace Olive\MySQLi\Views;

use Olive\MySQLi\View;

class ViewTest extends View {

    /**
     * @return string
     */
    public static function table() {
        return 'vw_test';
    }
}
