<?php

namespace abp\controller;

use abp\core\Controller;
use Abp;

/**
 * Class ConsoleController
 * @package component
 */
class ConsoleController extends Controller
{
    const NO_INTERACTION = '--no-interaction';

    protected $isInteraction = true;

    protected $params;
    protected $fullParams;

    public function beforeAction(): bool
    {
        if (php_sapi_name() !== 'cli') {
            Abp::debug('This script can only be run from the console.');
            exit();
        }

        $params = Abp::argv();
        $paramsSort = [];
        $fullParams = [];
        unset($params[0]);
        foreach ($params as $param) {
            $temp = explode('=', $param);
            if (count($temp) == 2) {
                $fullParams[$temp[0]] = $temp[1];
            }
            $paramsSort[] = $param;

        }
        $this->params = $paramsSort;
        $this->fullParams = $fullParams;

        if (isset($this->params[0]) && $this->params[0] == self::NO_INTERACTION) {
            $this->isInteraction = false;
        }

        return parent::beforeAction(); // TODO: Change the autogenerated stub
    }

    /**
     * @param mixed $object
     */
    public function _print($object)
    {
        if ($this->isInteraction) {
            print_r($object); echo PHP_EOL;
        }
    }

    public function _printException(\Throwable $e)
    {
        echo 'Class: ' . get_class($e) . PHP_EOL;
        echo 'Message: ' . $e->getMessage() . PHP_EOL;
        echo 'File: ' . $e->getFile() . PHP_EOL;
        echo 'Line: ' . $e->getLine() . PHP_EOL;
    }

    protected function askYNQuestion(string $textAsk, array $options = ['y', 'yes']): bool
    {
        if ($this->isInteraction) {
            $input = readline($textAsk);
        } else {
            return true;
        }
        if (!in_array($input, $options)) {
            return false;
        }
        return true;
    }
}