<?php
namespace abp\core;

ini_set("display_errors", "off");
error_reporting(E_ALL);

use abp\exception\NotFoundException;
use abp\core\Controller;
use Abp;
use component\Logger;

class ErrorHandler
{
    private const PHP_FATAL_ERRORS = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
    ];

    private static function parseArray(array $array): string
    {
        if (empty($array)) {
            return '';
        }

        return self::parseArgs($array);
    }

    public static function parseArgs(?array $args): string
    {
        if ($args === null) {
            return '';
        }
        $argsString = '';
        foreach ($args as $key => $arg) {
            $argString = '';
            if (!is_int($key)) {
                $argString = "'$key' => ";
            }
            switch (true) {
                case is_object($arg);
                    $argString .= get_class($arg);
                    break;
                case is_bool($arg);
                    $argString .= $arg;
                    break;
                case is_array($arg);
                    $argString .= '[' . self::parseArray($arg) . ']';
                    break;
                default:
                    $argString .= "'$arg'";
            }
            $argsString .= $argString . ', ';
        }
        return mb_substr($argsString, 0, -2);
    }

    /**
     * @param \Exception $exception
     */
    public static function exceptionHandler($exception): void
    {
        if (php_sapi_name() === 'cli') {
            echo 'Uncaught Error: ' . $exception->getMessage() . ' in '. $exception->getFile() . ':' . $exception->getLine() . PHP_EOL;
            echo 'Stack trace:' . PHP_EOL;
            echo $exception->getTraceAsString() . PHP_EOL;
            echo 'thrown in ' . $exception->getFile() . ':' . $exception->getLine() . PHP_EOL;
            exit();
        }
        $config = Abp::$config['app'];
        switch ($config['debug']) {
            case 'false':
                (new Controller())->renderSystemError($exception);
                exit();
            default:
                (new Controller())->renderTraceSystemError($exception);
                exit();
        }
    }

    public static function fatalError(array $error): void
    {
        $config = Abp::$config['app'];
        if (php_sapi_name() === 'cli') {
            print_r($error); exit();
        }
        switch ($config['debug']) {
            case 'false':
                (new Controller())->renderSystemError(new \Exception('Unknown error.'));
                exit();
            default:
                (new Controller())->renderTraceFatalError($error);
                exit();
        }
    }
}
set_exception_handler(function ($exception) {
    ErrorHandler::exceptionHandler($exception);
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error === null || !in_array($error['type'] ?? '', self::PHP_FATAL_ERRORS)) {
        return;
    }
    ErrorHandler::fatalError($error);
});