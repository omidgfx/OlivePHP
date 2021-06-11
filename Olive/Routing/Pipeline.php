<?php namespace Olive\Routing;


use Exception;
use Olive\Exceptions\MethodNotAllowedException;
use Olive\Exceptions\NotFoundException;
use Olive\Exceptions\ResultExpectedException;
use Olive\Http\Middleware;
use Olive\Http\Request;
use Olive\Http\Results\ActionResult;
use ReflectionClass;
use ReflectionMethod;
use Stringable;

abstract class Pipeline
{
    /**
     * @param $dispatcherResult
     * @throws \Olive\Exceptions\BadRequestException
     * @throws \Olive\Exceptions\MethodNotAllowedException
     * @throws \Olive\Exceptions\NotFoundException
     * @throws \Olive\Exceptions\ResultExpectedException
     */
    public static function follow($dispatcherResult): void {
        switch ($dispatcherResult[0]) {
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw MethodNotAllowedException::make($dispatcherResult[1] ?? []);
            case Dispatcher::FOUND:
                /**@var Handler $handler */
                [, $handler, $variables] = $dispatcherResult;
                $middlewares = $handler->middlewares;
                $callable    = $handler->callable;
                self::call($callable, $variables, $middlewares);
                break;
            case Dispatcher::NOT_FOUND:
            default:
                throw new NotFoundException;
        }

    }

    /**
     * @throws \Olive\Exceptions\BadRequestException
     * @throws \Olive\Exceptions\ResultExpectedException
     */
    private static function call($callable, $variables, $middlewares): void {

        # handle middlewares
        $goNext = self::handleMiddleWares($middlewares, $variables);
        if (!$goNext)
            return;

        # handle callable
        self::handleCallable($callable, $variables);
    }

    /**
     * @throws \Olive\Exceptions\BadRequestException
     * @throws \Olive\Exceptions\ResultExpectedException
     */
    private static function handleMiddleWares($middlewares, $variables): bool {
        if (is_array($middlewares) && count($middlewares) > 0) {
            /** @var \Olive\Http\Middleware $middleware */
            foreach ($middlewares as $middleware) {
                if (is_subclass_of($middleware, Middleware::class)) {
                    try {
                        $reflection = new ReflectionMethod($middleware, 'handle');
                        $request    = self::buildRequestInstance($reflection, $variables);
                    } catch (Exception) {
                        $request = new Request($variables);
                    }

                    $request->validate();

                    $response = $middleware::handle($request, $variables);
                    if ($response !== true) {
                        self::respond($response);
                        return false;
                    }
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * @throws \Olive\Exceptions\BadRequestException
     * @throws \Olive\Exceptions\ResultExpectedException
     */
    private static function handleCallable($callable, $variables): void {

        if (is_array($callable) && count($callable) === 2) {
            [$class, $method] = $callable;

            try {
                $reflection = new ReflectionMethod($class, $method);
                $request    = self::buildRequestInstance($reflection, $variables);
            } catch (Exception) {
                $request = new Request($variables);
            }

            $request->validate();
            $obj      = new $class;
            $response = $obj->{$method}($request, $variables);
            self::respond($response);
            return;
        }

        $callable(new Request($variables), $variables);

    }

    private static function buildRequestInstance(ReflectionMethod $reflection, array $variables = []): Request {
        $pars    = $reflection->getParameters();
        $request = null;
        if (count($pars) > 0) {
            $param = $pars[0];
            try {
                $type  = $param->getType();
                $param = $type && !$type->isBuiltin()
                    ? new ReflectionClass($type->getName())
                    : null;
            } catch (Exception) {
                $param = null;
            }
            if ($param && $param->isSubclassOf(Request::class)) {
                $param = $param->getName();
                /** @var Request $request */
                $request = new $param($variables);
            }
        }
        return $request ?? new Request($variables);
    }

    /**
     * @throws \Olive\Exceptions\ResultExpectedException
     */
    private static function respond($var): void {

        if ($var instanceof ActionResult) {
            $var->executeResult();
        } else {
            $type = strtolower(gettype($var));
            if (in_array($type, ['integer', 'double', 'string', 'null'])
                || ($type === 'object' && method_exists($var, '__toString'))) {
                print $var;
            } else {
                throw new ResultExpectedException;
            }
        }

    }
}