<?php

namespace abp\controller;

use abp\component\StringHelper;
use abp\controller\ConsoleController;
use Abp;

/**
 * Class CrudController
 * @package console\controller
 */
class CrudController extends ConsoleController
{
    const TEMPLATE_FOLDER = 'crud/template';

    const NO_CONTROLLER = '--no-controller';
    const NO_MODEL = '--no-model';
    const NO_QUERY = '--no-query';
    const NO_SCHEMA = '--no-schema';

    public function createAction()
    {
        if (!isset($this->params[0])) {
            return 'The crud name cannot be empty.';
        }
        $crudName = $this->params[0];
        $isCreate = ['controller' => 'controller', 'model' => 'model', 'query' => 'model/query', 'schema' => 'model/schema'];
        $helpString = '';
        unset($this->params[0]);
        foreach ($this->params as $param) {
            switch ($param) {
                case self::NO_CONTROLLER:
                    $isCreate['controller'] = false;
                    break;
                case self::NO_MODEL:
                    $isCreate['model'] = false;
                    break;
                case self::NO_QUERY:
                    $isCreate['query'] = false;
                    break;
                case self::NO_SCHEMA:
                    $isCreate['schema'] = false;
                    break;
            }
        }

        foreach ($isCreate as $key => $create) {
            if (!$create) {
                continue;
            }
            $parseCrudName = explode('/', $crudName);
            if (count($parseCrudName) === 1) {
                $crudName = '/' . $parseCrudName[0];
            }
            $folderName =  explode('/', $crudName)[0];
            if (!empty($folderName)) {
                $folderName .= '/';
            }
            if ($key !== 'controller') {
                $className = ucfirst(explode('/', $crudName)[1]);
            } else {
                $className = ucfirst(explode('/', $crudName)[1]) . ucfirst($key);
            }
            $createName = "$folderName$create/$className.php";
            $templateName = __DIR__ . '/../' . self::TEMPLATE_FOLDER . '/' . ucfirst($key). '.template';
            $fileTemplate = file_get_contents($templateName);
            $replace = [
                empty($folderName) ? '' : "$folderName\\",
                $className,
                empty($folderName) ? '' : ucfirst($folderName),
                StringHelper::conversionFilename($className),
            ];
            $subject = [
                '$namespace',
                '$classname',
                '$extendsclassname',
                '$tablename',
            ];
            $fileRender = str_replace($subject, $replace, $fileTemplate);
            $fileCreate = fopen($createName, 'w');
            fwrite($fileCreate, $fileRender);
            fclose($fileCreate);
            $this->_print(ucfirst($key) .  " \"$className\" was created.");
        }
        return 'Success.';
    }
}