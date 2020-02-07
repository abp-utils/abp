<?php

namespace abp\core;

use abp\component\StringHelper;
use abp\exeption\NotFoundExeption;
use Abp;

class Router
{
    const CONTROLLER_FOLDER = 'controller';
    const MIGRATE_FOLDER = 'migrate';

    private static $admin = false;
    private static $api = false;
    private static $console = false;

    private static $param = [];


    /**
     * @throws NotFoundExeption
     */
    public static function init() {
        if (!self::isConsole()) {
            self::isAdmin();
            self::isApi();
        } else {
            Abp::$requestString = '/'.$_SERVER['argv'][1];
        }

        $request = StringHelper::parseRequest(self::checkAliases(Abp::$requestString), self::$admin, self::$api);
        if (!$request) {
            throw new NotFoundExeption('Страница не найдена.');
        }

        if (!empty(Abp::$requestGet)) {
            self::$param = Request::get();
        }
        self::applyRoute($request);
    }

    /**
     * @param array $request
     * @return bool
     * @throws NotFoundExeption
     */
    public static function applyRoute($request)
    {
        $folder = self::CONTROLLER_FOLDER;
        if (self::$admin) {
            $folder =  self::$admin . '\\' . $folder;
        }
        if (self::$api) {
            $folder = self::$api . '\\' .$folder;
        }
        if (self::$console) {
            $folder = self::$console . '\\' .$folder;
        }
        $controllerFull = "$folder\\{$request['parse']['controller']}";
        $actionFull = $request['parse']['action'];
        if (!class_exists($controllerFull)) {
            throw new NotFoundExeption('Страница не найдена.');
        }
        $controller = new $controllerFull();
        $controller->controller = $request['origin']['controller'];
        $controller->action = $request['origin']['action'];
        if (!method_exists($controller, $actionFull)) {
            throw new NotFoundExeption('Страница не найдена.');
        }
        if (!$controller->beforeAction()) {
            return false;
        }
        if (!empty(self::$param)) {
            $output = $controller->$actionFull(self::$param);
        } else {
            $output = $controller->$actionFull();
        }
        $controller->afterAction();
        if (self::$console && !empty($output)) {
            exit($output . PHP_EOL);
        }
    }

    /**
     * @param string $request
     * @return string
     */
    public static function checkAliases($request)
    {
        return isset(Abp::$config['router']['alias'][$request]) ? Abp::$config['router']['alias'][$request] : (isset(Abp::$config['router']['alias'][substr($request, 1)]) ? '/'.Abp::$config['router']['alias'][substr($request, 1)] : $request);
    }

    public static function isAdmin()
    {
        $temp = explode('/', Abp::$requestString);
        $admin = isset(Abp::$config['router']['admin']) ? Abp::$config['router']['admin'] : false;
        if ($admin) {
            if ($temp[1] === $admin) {
                if (count($temp) !== 4) {
                    return false;
                }
                self::$admin = $admin;
                return;
            }
        }
        return false;
    }

    public static function isApi()
    {
        $temp = explode('/', Abp::$requestString);
        $api = isset(Abp::$config['router']['api']) ? Abp::$config['router']['api'] : false;
        if ($api) {
            if ($temp[1] === $api) {
                if (count($temp) !== 4) {
                    return false;
                }
                self::$api = $api;
                return;
            }
        }
        return false;
    }

    public static function isConsole()
    {
        if (php_sapi_name() !== 'cli') {
            return false;
        }

        $temp = explode('/', $_SERVER['argv'][1]);
        if (count($temp) !== 2) {
            return false;
        }
        self::$console = 'console';
        return true;
    }
}