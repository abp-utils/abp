<?php

namespace apb\core;

use apb\component\StringHelper;
use Abp;

class Model
{
    private $_attributes = [];

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
    }
}