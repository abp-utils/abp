<?php

namespace abp\database;

use abp\component\Api;
use abp\database\Query;

class ActiveRecord extends Query
{
    public $_isNewRecord = false;

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
        $attributes = [];
        $values = [];

        foreach ($this->_changeAttributes as $key => $value) {
            $attributes[] = $key;
            $values[] = $value === false ? null : $value;
        }

        if (!$this->_isNewRecord) {
            $identityRecord = array_slice($this->_attributes, 0 , 1);
            return $this->update($attributes, $values)->where($identityRecord)->commandExec();
        }
        return $this->insert($attributes, $values);
    }
}