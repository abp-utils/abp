<?php

namespace abp\core;

use abp\component\StringHelper;
use Abp;

class Model
{
    protected $_attributes = [];
    protected $_changeAttributes = [];

    protected $_tableName = null;

    public function __construct($class, $params = [])
    {
        $this->_tableName = StringHelper::conversionFilename($class);
        $this->_attributes = $params;
    }

    public function __get($name)
    {
        if (!isset($this->_attributes[$name])) {
            throw new \InvalidArgumentException("Свойство $name не существует в модели " . self::class);
        }
        return $this->_attributes[$name];
    }

    public function __set($name, $value)
    {
        if (!isset($this->_attributes[$name])) {
            throw new \InvalidArgumentException("Свойство $name не существует в модели " . self::class);
        }
        $this->_attributes[$name] = $value;
        $this->_changeAttributes[$name] = $value;
    }

    public function __debugInfo()
    {
        return [
            '_attributes:protected' => $this->_attributes,
            '_changeAttributes:protected' => $this->_changeAttributes,
        ];
    }
}