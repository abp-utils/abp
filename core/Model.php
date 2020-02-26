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
    protected $_attributes = [];
    protected $_changeAttributes = [];
    protected $_unsetAttributes = [];

    protected $_tableName = null;
    
    /**
     * Model constructor.
     * @param string $class
     * @param array $params
     */
    public function __construct($class, $params = [])
    {
        $this->_tableName = StringHelper::conversionFilename($class);
        $this->_attributes = $params;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
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
        $this->_attributes[$name] = $value;
        $this->_changeAttributes[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_attributes[$name]);
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
}