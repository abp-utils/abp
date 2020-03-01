<?php

namespace abp\core;

use abp\component\Form;
use abp\component\StringHelper;
use Abp;

/**
 * Class Model
 * @package abp\core
 */
class Model extends Form
{
    const MODEL_DEFAULT_FOLDER = '\model\\';

    protected $_attributes = [];
    protected $_changeAttributes = [];
    protected $_unsetAttributes = [];
    protected $_relations = [];
    protected $_relationsClass = [];

    protected $_tableName = null;
    
    /**
     * Model constructor.
     * @param string $class
     * @param array $params
     */
    public function __construct($class, $params = [])
    {
        $this->_tableName = StringHelper::conversionFilename($class);
        $paramsSave = $params;
        foreach ($params as $field => $value) {
            $fieldInfo = explode('.', $field);
            if (count($fieldInfo) == 1) {
                continue;
            }
            if ($fieldInfo[0] == $this->_tableName) {
                unset($paramsSave[$field]);
                $paramsSave[$fieldInfo[1]] = $value;
            } else {
                $className = self::MODEL_DEFAULT_FOLDER . StringHelper::revertConversionFilename($fieldInfo[0]);
                $this->_relations[$className][$fieldInfo[1]] = $value;
                unset($paramsSave[$field]);
            }
        }

        $this->_attributes = $paramsSave;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $modelClassname = self::MODEL_DEFAULT_FOLDER . $name;
        if (isset($this->_relations[$modelClassname]) && class_exists($modelClassname)) {
            if (!isset($this->_relationsClass[$modelClassname])) {
                $this->_relationsClass[$modelClassname] = new $modelClassname($modelClassname, $this->_relations[$modelClassname]);
            }
            return $this->_relationsClass[$modelClassname];
        }
        //var_dump(StringHelper::checkUpperCaseFirstLetter($name)); echo $name; echo '<br>';
        if (!in_array($name, array_keys($this->_attributes))) {
            throw new \InvalidArgumentException("Свойство $name не существует в модели " . self::class);
        }
        return $this->_attributes[$name];
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        if (!in_array($name, array_keys($this->_attributes))) {
            throw new \InvalidArgumentException("Свойство $name не существует в модели " . self::class);
        }
        if ($this->_attributes[$name] != $value) {
            $this->_attributes[$name] = $value;
            $this->_changeAttributes[$name] = $value;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return in_array($name, array_keys($this->_attributes));
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        if (!in_array($name, array_keys($this->_attributes))) {
            throw new \InvalidArgumentException("Свойство $name не существует в модели " . self::class);
        }
        $this->_unsetAttributes[$name] = $value;
        unset($this->_attributes[$name]);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            '_attributes:protected' => $this->_attributes,
            '_relations:protected' => $this->_relations,
            '_changeAttributes:protected' => $this->_changeAttributes,
            '_unsetAttributes:protected' => $this->_unsetAttributes,
        ];
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * @return array
     */
    public function getChangeAttributes()
    {
        return $this->_changeAttributes;
    }

    /**
     * 
     */
    public function getUnsetAttributes()
    {
        $this->_unsetAttributes;
    }

    public function load($data)
    {
        if (!isset($data[$this->_tableName])) {
            return false;
        }

        $modelLoad = $data[$this->_tableName];

        foreach ($modelLoad as $field => $value) {
            if (!isset($this->$field)) {
                continue;
            }

            $this->$field = $value;
        }

        if (!empty($this->getChangeAttributes())) {
            return true;
        }

        return false;
    }

    public function attributeLabels()
    {

    }

    public function changingAttributes()
    {

    }

    public static function relation()
    {

    }
}