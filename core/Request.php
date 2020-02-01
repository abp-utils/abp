<?php

namespace app\core;

use Abp;

class Request
{
    const GET = 'GET';
    const POST = 'POST';

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === self::GET;
    }
    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === self::POST;
    }

    public static function get()
    {
        if (isset($_GET['q']) && $_GET['q'] === Abp::$requestString) {
            unset($_GET['q']);
        }
        return $_GET;
    }

    public static function post()
    {
        return $_POST;
    }

    public static function getVar($var)
    {
        if (!isset(self::get()[$var])) {
            return false;
        }
        return htmlspecialchars(self::get()[$var]);
    }
    public static function postVar($var)
    {
        if (!isset(self::post()[$var])) {
            return false;
        }
        return htmlspecialchars(self::post()[$var]);
    }
}