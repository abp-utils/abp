<?php

namespace app\database;

use app\database\Database;
use app\database\Query;
use app\core\Model;
use Abp;

class ActiveQuery extends Query
{
    private $modelClass;
    private $where;

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

    /**
     * @return object|bool|null
     */
    public function one()
    {
        $modelClass = $this->modelClass;
        $data = parent::one();
        if ($data === false) {
            return false;
        }
        return new $modelClass(get_called_class(), $data);
    }

    /**
     * @return array|bool|null
     */
    public function all()
    {
        $modelClass = $this->modelClass;
        $data = parent::all();
        if ($data === false) {
            return false;
        }
        $models = [];
        foreach ($data as $modelData) {
            $models[] = new $modelClass(get_called_class(), $modelData);
        }
        return $models;
    }
}