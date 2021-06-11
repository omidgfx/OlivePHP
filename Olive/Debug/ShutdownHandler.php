<?php namespace Olive\Debug;


use Olive\Exceptions\FatalErrorException;

class ShutdownHandler
{
    /**
     * @throws FatalErrorException
     */
    public static function handler(): void {
        if (($error = error_get_last()) !== null) {
            throw FatalErrorException::makeByError(
                code: (int)($error['type'] ?? 0),
                message: $error['message'],
                file: $error['file'],
                line: $error['line'],
            );
        }
    }
}