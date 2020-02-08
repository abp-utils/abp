<?php

namespace abp\database;

use abp\component\Api;
use abp\database\Query;

class ActiveRecord extends Query
{
    private $_isNewRecord = false;

    public function __construct($class = null, $params = [])
    {
        if ($class !== null) {
            parent::__construct($class, $params);
            return;
        }

        $this->_isNewRecord = true;
        $class = get_called_class();
        parent::__construct($class, $params);
        $describe = $this->describe();
        $schema = [];
        foreach ($describe as $value) {
            $schema[$value['Field']] = false;
        }
        parent::__construct($class, $schema);
    }

    public function save()
    {
        if (!$this->_isNewRecord) {
            return false;
        }
        $attributes = [];
        $values = [];
        foreach ($this->_attributes as $key => $value) {
            $attributes[] = $key;
            $values[] = $value === false ? null : $value;
        }
        return $this->insert($attributes, $values);
    }
}