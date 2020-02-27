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

    private $modals = [];
    private $notification = [];

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
                require_once self::VIEW_TEMPLATE_FOLDER . 'head.php';
                foreach ($this->modals as $modal => $params) {
                    extract($params);
                    require_once self::VIEW_FOLDER . $modal . '.php';
                }
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

    protected function renderModal($view, $param = [])
    {
        $this->modals[$view] = $param;
    }

    protected function addError($text = 'Произошла ошибка.')
    {
        $this->addNotification($text, 'danger');
    }

    protected function addWarning($text = 'Произошла некритическая ошибка.')
    {
        $this->addNotification($text, 'warning');
    }

    protected function addSuccess($text = 'Успешно.')
    {
        $this->addNotification($text, 'success');
    }

    protected function addNotification($text, $type)
    {
        $this->notification[$type . '_' . uniqid()] = $text;
    }
}