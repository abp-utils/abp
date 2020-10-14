<?php

namespace abp\core;

use Abp;
use abp\core\ErrorHandler;
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
    const NOTIFICATIONS_PREFIX = 'notifications';

    public $controller;
    public $action;
    public $title = null;

    private $modals = [];
    private $notifications = [];

    private $js = null;

    public function beforeAction(): bool
    {
        return true;
    }

    public function afterAction(): bool
    {
        return true;
    }

    protected function redirect(string $url = ''): void
    {
        Abp::redirect(Abp::url($url));
    }

    protected function redirectAbsolute(string $url): void
    {
        Abp::redirect($url);
    }

    /**
     * Use only framework.
     */
    public function renderSystemError(\Throwable $exception): void
    {
        if (file_exists(Controller::VIEW_TEMPLATE_FOLDER . 'head.php')) {
            require Controller::VIEW_TEMPLATE_FOLDER . 'head.php';
        }
        if (file_exists(Controller::VIEW_TEMPLATE_FOLDER . 'header.php')) {
            require Controller::VIEW_TEMPLATE_FOLDER . 'header.php';
        }
        if ($exception instanceof NotFoundException) {
            echo '<div class="container"><div class="site-error"><h1></h1><div class="alert alert-danger">' . $exception->getMessage() . '</div></div></div>';
        } else {
            echo '<div class="container"><div class="site-error"><h1></h1><div class="alert alert-danger">Произошла неизвестная ошибка. Попробуйте позже.</div></div></div>';
        }
        if (file_exists(Controller::VIEW_TEMPLATE_FOLDER . 'footer.php')) {
            require Controller::VIEW_TEMPLATE_FOLDER . 'footer.php';
        }
    }

    /**
     * Use only framework.
     */
    public function renderTraceSystemError(\Throwable $exception): void
    {
        $dir = Abp::rootFolder();
        $exceptionName = get_class($exception);
        $exceptionText = $exception->getMessage();
        $exceptionTraceDebug = $exception->getTrace();
        $trace = [];
        $trace[0]['text'] = 'in ' . $exception->getFile();
        $trace[0]['line'] = $exception->getLine();
        foreach ($exceptionTraceDebug as $key => $exceptionTrace) {
            $trace[($key + 1)]['text'] = 'in ' . $exceptionTrace['file'] . ' – ' . ($exceptionTrace['class'] ?? '') . ($exceptionTrace['type'] ?? '') . $exceptionTrace['function'] . '(' . ErrorHandler::parseArgs($exceptionTrace['args'] ?? null) . ')';
            $trace[($key + 1)]['line'] = $exceptionTrace['line'];
        }
        require __DIR__ . "/../view/ErrorHandler.php";
    }

    /**
     * Use only framework.
     */
    public function renderTraceFatalError(array $error): void
    {
        Abp::debug($error); exit();
    }

    protected function render(array $param = [], ?string $view = null, bool $isPartical = false): void
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
                require self::VIEW_TEMPLATE_FOLDER . 'head.php';
                foreach ($this->modals as $modal => $params) {
                    extract($params);
                    require self::VIEW_FOLDER . $modal . '.php';
                }
                require self::VIEW_TEMPLATE_FOLDER . 'header.php';
            }

            require self::VIEW_FOLDER . $this->controller . '/' . ($view ?? $this->action) . '.php';

            if (!$isPartical) {
                require self::VIEW_TEMPLATE_FOLDER . 'footer.php';
            }
            $out = ob_get_clean();
        } catch (\Throwable $e) {
            ob_clean();
            throw $e;
        }
        echo $out;

    }

    protected function renderPartical(array $param = [], ?string $view = null): void
    {
        $this->render($param, $view, true);
    }

    protected function renderModal(string $view, array $param = []): void
    {
        $this->modals[$view] = $param;
    }

    protected function addError(string $text = 'Произошла ошибка.'): void
    {
        $this->addNotification($text, 'danger');
    }

    protected function addWarning(string $text = 'Произошла некритическая ошибка.'): void
    {
        $this->addNotification($text, 'warning');
    }

    protected function addSuccess(string $text = 'Успешно.'): void
    {
        $this->addNotification($text, 'success');
    }

    protected function addInfo(string $text = 'Уведомление.'): void
    {
        $this->addNotification($text, 'primary');
    }

    protected function addNotification(string $text, string $type): void
    {
        $uniqid = uniqid();
        Abp::setCookie(self::NOTIFICATIONS_PREFIX . '_' . $type . '_' . $uniqid, $text);
        $this->notifications[self::NOTIFICATIONS_PREFIX . '_' . $type . '_' . $uniqid] = $text;
    }

    private function getNotificationsOnCookie(): array
    {
        $notificatios = [];
        $cookies = Abp::getCookie();
        foreach ($cookies as $name => $value) {
            if (strpos($name, self::NOTIFICATIONS_PREFIX) !== false) {
                $notificatios[$name] = $value;
            }
        }
        return $notificatios;
    }

    public function showNotification(): string
    {
        $notificatiosCookie = $this->getNotificationsOnCookie();
        $notificatiosLocal = $this->notifications;
        $notificatios = array_merge($notificatiosCookie, $notificatiosLocal);
        $notificatiosText = '';
        foreach ($notificatios as $typeExp => $text) {
            $type = explode('_', $typeExp)[1];
            $notificatiosText .= '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' . $text;
            $notificatiosText .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            $notificatiosText .= '<span aria-hidden="true">&times;</span>';
            $notificatiosText .= '</button>';
            $notificatiosText .= '</div>';
            Abp::dropCookie($typeExp);
        }
        return $notificatiosText;
    }

    public function registerJS(string $js): void
    {
        if ($this->js === null) {
            $this->js = $js;
        } else {
            $this->js .= $js;
        }
    }

    public function getJS(): string
    {
        if ($this->js === null) {
            return '';
        }
        return $this->js;
    }
}

