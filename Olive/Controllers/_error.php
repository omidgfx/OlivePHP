<?php namespace Olive\Routing;


use Olive\Exceptions\ApiException;
use Olive\Exceptions\H500;
use Olive\Exceptions\OliveException;

class _error extends Controller {
    public function fnIndex($args = []) {


        /** @var OliveException $e */;
        if(!isset($args['exception']) || !(($e = $args['exception']) instanceof OliveException)) {
            //echo 'FATAL ERROR';
            $e = $args['exception'] = new H500;
            // return;
        }

        $code  = $e->getCode();
        $rcode = $code;
        if(!$code || !in_array($code, [400, 401, 402, 403, 404, 500, 501, 503]))
            $rcode = 500;

        self::setHttpResponseCode($rcode);

        if($e instanceof ApiException) {
            echo $e->getMessage();
            return;
        } else {

            $var = [
                'exception' => $e,
                'code'      => DEBUG_MODE ? $code : $rcode,
            ];

            self::renderView(DEBUG_MODE ? '_error.debug' : '_error', $var);

        }
    }


}
