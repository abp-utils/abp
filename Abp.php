<?php
require 'core/Autoload.php';
require 'core/ErrorHandler.php';

use abp\database\Database;
use abp\core\Router;
use abp\core\Request;

/**
 * Class Abp
 *
 * @staticvar array $config
 * @staticvar string $url
 * @staticvar string $domain
 * @staticvar string $protocol
 * @staticvar string $request
 * @staticvar string $requestGet
 * @staticvar Database $db
 */
class Abp
{
    const NAME = 'abp';

    const TIMEZONE_DEFAULT = 'Europe/Moscow';

    public static $config;

    public static $url;
    public static $domain;
    public static $protocol;
    public static $requestString;
    public static $requestGet;

    /* @var Database $db */
    public static $db;

    private static $argv = [];

    public static function init($config)
    {
        self::$config = $config;
        self::initTimeZone();
        if (php_sapi_name() !== 'cli') {
            self::setUrl();
        }
        self::setDb();

        Router::init();
    }

    public static function debug($message, $console = false, $var_dump = false, $return = false)
    {
        if ($console) {
            print_r($message); echo PHP_EOL;
            return;
        }
        if (!$var_dump) {
            if ($return) {
                return print_r($message, true);
            }
            echo '<pre>';
            print_r($message);
            echo '</pre>';
            return;
        }

        if (!$return) {
            echo '<pre>';
            var_dump($message);
            echo '</pre>';
            return;
        }

        ob_start();
        var_dump($message);
        return ob_get_clean();
    }

    public static function argv()
    {
        if (empty(self::$argv)) {
            $argv = $_SERVER['argv'];
            $argvSort = [];
            unset($argv[0]);
            foreach ($argv as $arg) {
                $argvSort[] = $arg;
            }
            self::$argv = $argvSort;
        }
        return self::$argv;
    }

    public static function server()
    {
        return $_SERVER;
    }

    public static function post()
    {
        return $_POST;
    }

    public static function rootFolder()
    {
        return self::server()['DOCUMENT_ROOT'];
    }

    /**
     * @param string $url
     */
    public static function redirect($url)
    {
        header("Location: $url");
    }

    /**
     * @param string $request
     * @return string
     */
    public static function url($request)
    {
        return self::$url .'/' . $request;
    }

    /**
     * @param null $param
     * @return bool|mixed
     */
    public static function getCookie($param = null)
    {
        if ($param === null) {
            return $_COOKIE;
        }
        return htmlspecialchars($_COOKIE[$param]) ?? false;
    }

    public static function setCookie($key, $value, $time = null, $path = '/')
    {
        if ($time === null) {
            $time = time()+3600*24*365*10;
        }
        setcookie($key, $value, $time, '/');
    }

    public static function dropCookie($key)
    {
        setcookie($key, '', time() - 3600, '/');
    }

    private static function initTimeZone()
    {
        if (isset(self::$config['app']['timezone'])) {
            date_default_timezone_set(self::TIMEZONE_DEFAULT);
        }
    }

    private static function setUrl()
    {
        self::$domain = $_SERVER['HTTP_HOST'];
        self::$protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'http';
        self::$url = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : self::$protocol."://{$_SERVER['HTTP_HOST']}";
        self::$requestString = isset(self::server()['REQUEST_URI']) ? explode('?', self::server()['REQUEST_URI'])[0] : preg_replace('#q=/#', '', explode('&',$_SERVER['QUERY_STRING'])[0], 1);
        self::$requestGet = isset(self::server()['REQUEST_URI']) ? explode('?', self::server()['REQUEST_URI'])[1] ?? false : preg_replace('#q=/#', '', explode('&',$_SERVER['QUERY_STRING'])[1] ?? false, 1);
    }

    private static function setDb()
    {
        self::$db = Database::instance();
    }
}