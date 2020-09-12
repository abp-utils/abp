<?php

namespace abp\database;

use abp\database\Database;
use abp\database\Query;
use abp\core\Model;
use Abp;

class ActiveQuery extends Query
{
    /**
     * ActiveQuery constructor.
     * @param string $class
     * @param array $params
     */
    public function __construct($class, $params = [])
    {
        $this->modelClass = $class;
        parent::__construct($class, $params);
    }

    public function __debugInfo()
    {
        $vars = get_object_vars($this);
        unset($vars['_attributes']);
        return $vars;
    }

    /**
     * @return object|null
     */
    public function one()
    {
        $modelClass = $this->modelClass;
        $data = parent::one();
        if ($data === null) {
            return null;
        }
        return new $modelClass(get_called_class(), $data);
    }

    /**
     * @return array
     */
    public function all()
    {
        $modelClass = $this->modelClass;
        $data = parent::all();
        if (empty($data)) {
            return [];
        }
        $models = [];
        foreach ($data as $modelData) {
            $models[] = new $modelClass(get_called_class(), $modelData);
        }
        return $models;
    }
}
