<?php

namespace abp\core;

use Abp;

class Request
{
    const GET = 'GET';
    const POST = 'POST';

    /**
     * @return mixed
     */
    public static function method()
    {
        return self::server('REQUEST_METHOD');
    }

    /**
     * @return bool
     */
    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === self::GET;
    }

    /**
     * @return bool
     */
    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === self::POST;
    }

    /**
     * @return array
     */
    public static function get()
    {
        if (isset($_GET['q']) && $_GET['q'] === Abp::$requestString) {
            unset($_GET['q']);
        }
        return $_GET;
    }

    /**
     * @return array
     */
    public static function post()
    {
        return $_POST;
    }

    /**
     * @param string $var
     * @return bool|string
     */
    public static function getVar($var)
    {
        if (!isset(self::get()[$var])) {
            return false;
        }
        return htmlspecialchars(self::get()[$var]);
    }

    /**
     * @param string $var
     * @return bool|string
     */
    public static function postVar($var)
    {
        if (!isset(self::post()[$var])) {
            return false;
        }
        return htmlspecialchars(self::post()[$var]);
    }

    /**
     * @param null $param
     * @return array|string
     */
    public static function server($param = null)
    {
        if ($param === null) {
            return $_SERVER;
        }
        return $_SERVER[$param];
    }

    /**
     * @return string
     */
    public static function getUserIp()
    {
        return $ip = isset(self::server()['HTTP_X_REAL_IP']) ? self::server('HTTP_X_REAL_IP') : self::server('REMOTE_ADDR');
    }
}