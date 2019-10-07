<?php namespace Olive;

use Olive\Exceptions\OliveError;
use Olive\Exceptions\OliveFatalError;

abstract class Core
{

    #region Includers and boot handlers

    /**
     * @param string[] $modules
     * @throws OliveFatalError
     * @see  Core::requireModule()
     * @uses Core::requireModule()
     */
    public static function requireModules(array $modules) {
        foreach ($modules as $module)
            self::requireModule($module);
    }

    /**
     * ##RequireModule
     * Boot, Require and start modules
     *
     * ###Perform scenario (priority):
     * > 1. <font color="orange">`Olive/Support/`</font><b color="lime">`$module`</b><font color="orange">`.php`</font>
     * > 2. <font color="orange">`Olive/Support/`</font><b color="lime">`$module`</b><font color="orange">`/loader.php`</font>
     * > 3. {@see Core::boot Boot}s module directory from<br>
     *    <font color="orange">`Olive/Support/`</font><b color="lime">`$module`</b><font color="orange">`/`</font><br><br>
     * > 4. <font color="#ff8888">`App/Modules/`</font><b color="lime">`$module`</b><font color="#ff8888">`.php`</font>
     * > 5. <font color="#ff8888">`App/Modules/`</font><b color="lime">`$module`</b><font color="#ff8888">`/loader.php`</font>
     * > 6. {@see Core::boot Boot}s module directory from<br>
     *    <font color="#ff8888">`App/Modules/`</font><b color="lime">`$module`</b><font color="#ff8888">`/`</font>
     *
     *
     *
     * @param string $module
     * @throws OliveFatalError
     */
    public static function requireModule($module) {

        if (file_exists($path = "App/Modules/$module.php")) {
            /** @noinspection PhpIncludeInspection */
            require_once $path;
            return;
        }
        if (is_dir("App/Modules/$module")) {
            if (file_exists($path = "App/Modules/$module/loader.php")) {
                /** @noinspection PhpIncludeInspection */
                require_once $path;
                return;
            }
            static::boot("App/Modules/$module");
            return;
        }
        throw new OliveFatalError("Module not found '$module'");
    }

    /**
     * ##Boot given directory
     * Requires all php files in directory (recursively)
     * <div style="color:orange;padding-top:0">
     * * Sub-directories are first priority to boot
     * * Underscore prefix (_) has most priority in require
     * </font>
     *
     * @param $path
     */
    public static function boot($path) {

        if ($path === null) return;

        # read files and folders
        $list = glob("$path/*");
        if (count($list) === 0)
            return;
        $dirs = $files = [];

        foreach ($list as $item) {
            if (is_dir($item))
                $dirs[] = $item;
            elseif (strtolower(substr($item, -4)) === '.php')
                $files[] = $item;
        }
        unset($list);

        # sort
        $sorter = static function ($a, $b) {
            return strcmp(str_replace('_', 0, $a), str_replace('_', 0, $b));
        };
        usort($dirs, $sorter);
        usort($files, $sorter);

        # recursive call boot for folders
        foreach ($dirs as $dir)
            self::boot($dir);

        # require files
        foreach ($files as $item)
            /** @noinspection PhpIncludeInspection */
            require_once $item;
    }

    #endregion

    #region Error Handlers

    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @param null $errcontext
     * @throws OliveError
     */
    final public static function errorHandler($code, $message, $file, $line, $errcontext = null) {
        $e              = new OliveError;
        $e->code        = $code;
        $e->message     = $message;
        $e->file        = $file;
        $e->line        = $line;
        $e->{'context'} = $errcontext;
        throw $e;
    }

    final public static function shutdownHandler() {
        if (($error = error_get_last()) !== null) {
            $exception          = new OliveFatalError;
            $exception->code    = $error['type'];
            $exception->message = $error['message'];
            $exception->file    = $error['file'];
            $exception->line    = $error['line'];

            if (DEBUG_MODE) {
                echo "<div style='background:#fafafa;color:#777;margin: 10px;border: 1px solid #ddd;padding: 10px;border-radius: 8px'><h1><span style='color:red'>Fatal error captured:</span></h1>",
                '<pre>';
                /** @noinspection ForgottenDebugOutputInspection */
                print_r($error);
                echo '</pre></div>';
            } else {
                ob_clean();
                http_response_code(500);
                echo "<h1 style='color:red;text-align:center;'>FATAL ERROR</h1>";
            }
        }

    }

    #endregion

    #region App

    public static function startApp($path = 'App') {
        # read files and folders
        $list = glob("$path/*.boot", GLOB_ONLYDIR);
        if (count($list) === 0)
            return;
        # boot
        foreach ($list as $item)
            self::boot($item);

    }

    #endregion

}
