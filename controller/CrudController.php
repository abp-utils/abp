<?php

namespace abp\controller;

use abp\component\StringHelper;
use abp\controller\ConsoleController;
use Abp;
use abp\database\Code;
use abp\database\Construction;
use abp\database\Query;
use abp\database\Types;
use abp\exception\DatabaseException;

/**
 * Class CrudController
 * @package abp\controller
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
        $isCreate = ['schema' => 'model/schema', 'controller' => 'controller', 'model' => 'model', 'query' => 'model/query'];
        $helpString = '';
        unset($this->params[0]);
        foreach ($this->params as $param) {
            switch ($param) {
                case self::NO_SCHEMA:
                    $isCreate['schema'] = false;
                    break;
                case self::NO_CONTROLLER:
                    $isCreate['controller'] = false;
                    break;
                case self::NO_MODEL:
                    $isCreate['model'] = false;
                    break;
                case self::NO_QUERY:
                    $isCreate['query'] = false;
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
            $tableName = StringHelper::conversionFilename($className);
            $properties = '';
            $attributeLabels = '';

            if ($key === 'schema') {
                try {
                    $tableStrict = (new Query())->describe($tableName);
                } catch (DatabaseException $e) {
                    if ($e->getDbCode() == Code::SQL_TABLE_NOT_EXIST) {
                        return "Table `$tableName` does not exist.";
                    }
                }
                foreach ($tableStrict as $column) {
                    $properties .= ' * @property '
                        . Construction::getPhpTypeOnSqlType($this->prepareSqlType($column['Type'])).
                        ' $' . $column['Field'] . PHP_EOL;
                    $attributeLabels .= '            \''
                        . $column['Field']
                        . '\' => \''
                        . ($column['Key'] === Types::SQL_PRIMARY_KEY
                        ? '#'
                        : $column['Field']) . '\',' . PHP_EOL;
                }
            }
            $properties = rtrim($properties);
            $attributeLabels = rtrim($attributeLabels);
            $replace = [
                empty($folderName) ? '' : "$folderName\\",
                $className,
                empty($folderName) ? '' : ucfirst($folderName),
                $tableName,
                $properties,
                $attributeLabels,
            ];
            $subject = [
                '$namespace',
                '$classname',
                '$extendsclassname',
                '$tablename',
                '$properties',
                '$attributeLabels',
            ];
            $fileRender = str_replace($subject, $replace, $fileTemplate);
            $fileCreate = fopen($createName, 'w');
            fwrite($fileCreate, $fileRender);
            fclose($fileCreate);
            $this->_print(ucfirst($key) .  " \"$className\" was created.");
        }
        return 'Success.';
    }

    private function prepareSqlType(string $sqlType): string
    {
        return explode('(', $sqlType)[0];
    }
}