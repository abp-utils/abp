<?php

namespace abp\core;

use Abp;
use abp\exception\NotFoundException;

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

    /**
     * @return bool
     */
    public function beforeAction()
    {
        return true;
    }

    /**
     * @return bool
     */
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
     * @param string $url
     */
    protected function redirectAbsolute($url)
    {
        Abp::redirect($url);
    }

    /**
     * @param array $param
     * @param string|null $view
     * @param bool $isPartical
     *
     * @throws \Exception
     * @throws NotFoundException
     */
    protected function render($param = [], $view = null, $isPartical = false)
    {
        extract($param);
        try {
            ob_start();
            if (!$isPartical) {
                if (!file_exists(self::VIEW_TEMPLATE_FOLDER . 'header.php')) {
                    throw new NotFoundException('Шаблон header не найден.');
                }
                if (!file_exists(self::VIEW_TEMPLATE_FOLDER . 'footer.php')) {
                    throw new NotFoundException('Шаблон footer не найден.');
                }
            }
            if (!file_exists(self::VIEW_FOLDER . $this->controller . '/' . ($view ?? $this->action) . '.php')) {
                throw new NotFoundException('Шаблон '. ($view ?? $this->action) .' не найден.');
            }
            if (!$isPartical) {
                require_once self::VIEW_TEMPLATE_FOLDER . 'header.php';
            }
            require_once self::VIEW_FOLDER . $this->controller . '/' . ($view ?? $this->action) . '.php';
            if (!$isPartical) {
                require_once self::VIEW_TEMPLATE_FOLDER . 'footer.php';
            }
            $out = ob_get_clean();
        } catch (\Exception $e) {
            ob_clean();
            throw $e;
        }
        echo $out;

    }

    /**
     * @param array $param
     * @param string|null $view
     *
     * @throws \Exception
     * @throws NotFoundException
     */
    protected function renderPartical($param = [], $view = null)
    {
        $this->render($param, $view, true);
    }
}