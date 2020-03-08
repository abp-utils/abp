<?php

namespace abp\component;

class Session
{
    /**
     * @inheritDoc
     */
    public static function init()
    {
        session_start();
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function get($name)
    {
        if (!isset($_SESSION[$name])) {
            return false;
        }
    }

    /**
     * @param string $name
     * @param string $value
     * @return bool
     */
    public static function set($name, $value)
    {
        if (!isset($_SESSION[$name])) {
            return false;
        }

        $_SESSION[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function drop($name)
    {
        if (!isset($_SESSION[$name])) {
            return false;
        }

        unset($_SESSION[$name]);
    }
}
