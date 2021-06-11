<?php namespace Olive;

use manifest;
use Olive\Debug\ErrorHandler;
use Olive\Debug\ExceptionHandler;
use Olive\Debug\ShutdownHandler;
use Olive\Routing\Dispatcher;
use Olive\Routing\Pipeline;
use Olive\Routing\Route;

abstract class Kernel
{
    /**
     * @throws \Olive\Exceptions\BadRequestException
     * @throws \Olive\Exceptions\MethodNotAllowedException
     * @throws \Olive\Exceptions\NotFoundException
     * @throws \Olive\Exceptions\ResultExpectedException
     */
    public static function boot(): void {

        self::constants();

        self::references();

        self::defaults();

        self::overriders();

        self::security();

        self::routers();
    }


    private static function constants(): void {
        define('DEBUG_MODE', manifest::debug);
    }

    private static function references(): void {
        require_once "References.php";
    }

    private static function defaults(): void {
        date_default_timezone_set(manifest::default_timezone ?: 'GMT');
        mb_language('uni');
        mb_internal_encoding(manifest::default_encoding ?: 'UTF-8');
        error_reporting(DEBUG_MODE ? E_ALL : 0);
    }

    private static function overriders(): void {
        set_exception_handler([ExceptionHandler::class, 'catch']);
        /** @noinspection PhpExpressionResultUnusedInspection */
        set_error_handler([ErrorHandler::class, 'handler'], E_ALL);
        register_shutdown_function([ShutdownHandler::class, 'handler']);

    }

    private static function security(): void {

    }

    /**
     * @throws \Olive\Exceptions\BadRequestException
     * @throws \Olive\Exceptions\MethodNotAllowedException
     * @throws \Olive\Exceptions\NotFoundException
     * @throws \Olive\Exceptions\ResultExpectedException
     */
    private static function routers(): void {

        # find router sections
        $sections = glob(__DIR__ . '/../routers/*.php');
        array_map(static function ($section) {
            if (is_file($section))
                /** @noinspection PhpIncludeInspection */
                require_once $section;
        }, $sections);


        # get collector
        $collector = Route::getCollector();

        # data of collector
        $data = $collector->getData();

        # dispatch collected datum
        $dispatcher = new Dispatcher($data);
        $result     = $dispatcher->dispatch();

        Pipeline::follow($result);

    }
}