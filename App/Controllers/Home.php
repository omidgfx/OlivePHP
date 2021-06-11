<?php namespace App\Controllers;

use Olive\Http\Controller;

class Home extends Controller
{
    public function hello_world($request) {
        echo 'Hello World!';
    }
}