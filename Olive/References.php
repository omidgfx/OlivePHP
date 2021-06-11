<?php

use Olive\Util\Str;

if (!function_exists('getallheaders')) {
    function getallheaders(): array {
        $headers = [];
        foreach ($_SERVER as $name => $value)
            /** @noinspection PhpStrFunctionsInspection */
            /** @noinspection StrStartsWithCanBeUsedInspection */
            if (strpos($name, 'HTTP_') === 0)
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        return $headers;
    }
}

/**
 * @param $baseDir
 * @param $path
 * @return bool
 */
function isSubDirectoryOf($baseDir, $path): bool {
    $baseReal = realpath($baseDir);
    $pathReal = realpath($path);
    return Str::startsWith($baseReal, $pathReal);
}
//
//if (!interface_exists('Stringable')) {
//    interface Stringable
//    {
//        public function __toString(): string;
//    }
//}