<?php

namespace app\core;

use Abp;

/**
 * Class Controller
 * @package app\core
 *
 * @property string $controller
 * @property string $action
 * @property string $title
 */
class Controller
{
    const VIEW_FOLDER = 'view/';
    const VIEW_TEMPLATE_FOLDER = 'view/template/';

    public $controller;
    public $action;
    public $title = null;

    public function beforeAction()
    {
        return true;
    }

    public function afterAction()
    {
        return true;
    }
    /**
     * @param string $url
     */
    protected function redirect($url)
    {
        Abp::redirect(Abp::url($url));
    }

    /**
     * @param array $param
     * @param string|null $view
     * @throws \Exception
     */
    protected function render($param = [], $view = null)
    {
        extract($param);
        try {
            ob_start();
            require_once self::VIEW_TEMPLATE_FOLDER . 'header.php';
            require_once self::VIEW_FOLDER . $this->controller . '/' . ($view ?? $this->action) . '.php';
            require_once self::VIEW_TEMPLATE_FOLDER . 'footer.php';
            $out = ob_get_clean();
        } catch (\Exception $e) {
            throw $e;
        }
        echo $out;

    }
}