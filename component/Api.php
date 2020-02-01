<?php

namespace component;

use core\Request;
/**
 * Class Api
 * @package app\component
 */
class Api {

    const VERSION = '0.1';

    /**
     * @param bool $error
     * @param mixed $message
     * @return false|string
     */
    public static function returnMessage(bool $error, $data = '')
    {
        $array = [
            'error' => $error,
            'data' => $data,
        ];

        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }

    public static function message(bool $error, $data = '')
    {
        echo self::returnMessage($error, $data);
        exit();
    }
    public static function supportMethod()
    {
        return implode(', ', [
            Request::POST,
        ]);
    }
}