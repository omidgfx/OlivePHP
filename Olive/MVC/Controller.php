<?php namespace Olive\MVC;

use Olive\Routing\Route;

/**
 * @property-read Route $route
 * Class Controller
 * @package Olive\Controllers
 */
abstract class Controller
{

    #region Fields

    public $route;

    #endregion

    #region Abstracts

    abstract public function Index($args = []);

    #endregion

    #region Constructors

    public function __construct(Route $route) {
        $this->route = $route;
    }

    #endregion


}
