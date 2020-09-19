<?php

namespace abp\component;

use abp\exception\NotFoundException;
use abp\core\Controller;
use Abp;
use component\Logger;

class ErrorHandler
{
    private static function parseArray($array)
    {
        if (empty($array)) {
            return '';
        }

        return self::parseArgs($array);
    }

    public static function parseArgs($args)
    {
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
    public static function exception_handler($exception)
    {
        if (php_sapi_name() === 'cli') {
            echo 'PHP Fatal error: ' . $exception->getMessage() . 'in'. $exception->getFile() . ':' . $exception->getLine() . PHP_EOL;
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
}
set_exception_handler(function ($exception) {
    Logger::exception_handler($exception);
});