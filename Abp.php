<?php
require 'core/Autoload.php';
require 'core/ErrorHandler.php';

use abp\database\Database;
use abp\core\Router;
use abp\component\Session;

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

    public static $ip;
    public static $userAgent;

    /* @var Database $db */
    public static $db;

    /* @var Session $session */
    public static $session;

    private static $argv = [];

    public static $root;

    /**
     * @param array $config
     */
    public static function init($config)
    {
        self::$config = $config;
        self::setRoot();
        self::initTimeZone();
        if (php_sapi_name() !== 'cli') {
            self::setUrl();
        }
        self::setDb();
        self::setSession();

        self::setUserInfo();

        Router::init();
    }

    /**
     * @param string $message
     * @param bool $console
     * @param bool $var_dump
     * @param bool $return
     * @return false|string|true|void
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public static function server()
    {
        return $_SERVER;
    }

    /**
     * @return array
     */
    public static function post()
    {
        return $_POST;
    }

    /**
     * @return string
     */
    public static function rootFolder()
    {
        return self::server()['DOCUMENT_ROOT'];
    }

    public static function redirect(string $url)
    {
        header("Location: $url");
	    exit();
    }

    public static function url(string $request, bool $isFull = false): string
    {
        if ($request == '/') {
            return  '/';
        }
        if ($isFull) {
            return $request;
        }
        return '/' . $request;
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
        return isset($_COOKIE[$param]) ? htmlspecialchars($_COOKIE[$param]) : null;
    }

    /**
     * @param string $key
     * @param string $value
     * @param int|null $time
     * @param string $path
     */
    public static function setCookie($key, $value, $time = null, $path = '/')
    {
        if ($time === null) {
            $time = time()+3600*24*365*10;
        }
        setcookie($key, $value, $time, '/');
    }

    /**
     * @param string $key
     */
    public static function dropCookie($key)
    {
        setcookie($key, '', time() - 3600, '/');
    }

    /**
     * @inheritDoc
     */
    private static function initTimeZone()
    {
        if (!isset(self::$config['app']['timezone'])) {
            date_default_timezone_set(self::TIMEZONE_DEFAULT);
        } else {
            date_default_timezone_set(self::$config['app']['timezone']);
        }
    }

    /**
     * @inheritDoc
     */
    private static function setUrl()
    {
        self::$domain = $_SERVER['HTTP_HOST'];
        self::$protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME'] ?? 'http';
        self::$url = self::$protocol . "://{$_SERVER['HTTP_HOST']}";
        self::$requestString = isset(self::server()['REQUEST_URI']) ? explode('?', self::server()['REQUEST_URI'])[0] : preg_replace('#q=/#', '', explode('&',$_SERVER['QUERY_STRING'])[0], 1);
        self::$requestGet = isset(self::server()['REQUEST_URI']) ? explode('?', self::server()['REQUEST_URI'])[1] ?? false : preg_replace('#q=/#', '', explode('&',$_SERVER['QUERY_STRING'])[1] ?? false, 1);
    }

    /**
     * @inheritDoc
     */
    private static function setDb()
    {
        self::$db = Database::instance();
    }

    /**
     * @inheritDoc
     */
    private static function setSession()
    {
        self::$session = new Session();
    }

    /**
     * @inheritDoc
     */
    private static function setRoot()
    {
        if (php_sapi_name() !== 'cli') {
            $root = $_SERVER['DOCUMENT_ROOT'];
        } else {
            $exp = explode('/', $_SERVER['SCRIPT_FILENAME']);
            if (count($exp) > 1) {
                unset($exp[count($exp) - 1]);
            }
            $root = implode('/', $exp);
        }
        self::$root = $root . '/';
    }

    /**
     * @inheritDoc
     */
    private static function setUserInfo()
    {
        self::$ip = self::server()['REMOTE_ADDR'] ?? null;
        self::$userAgent = self::server()['HTTP_USER_AGENT'] ?? null;
    }
}



