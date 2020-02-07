<?php

namespace abp\database;

use abp\core\Model;
use Abp;

/**
 * Class Query
 * @package app\database
 *
 * @property string $_select;
 * @property string $_where;
 * @property string $_order;
 * @property string $_limit;
 */
class Query extends Model
{
    private $_update = '';
    private $_select = '';
    private $_where = '';
    private $_order = '';
    private $_limit = '';

    /**
     * Query constructor.
     * @param string $class
     * @param array $params
     */
    public function __construct($class, $params = [])
    {
        parent::__construct($class, $params);
    }

    /**
     * @param string|null $sql
     * @return array|bool
     */
    public function command($sql = null)
    {
        if ($sql === null) {
            $sql = $this->buildSql();
        }
        return Abp::$db->query($sql);
    }

    /**
     * @param string|null $sql
     * @return array|bool
     */
    public function commandExec($sql = null)
    {
        if ($sql === null) {
            $sql = $this->buildSql();
        }
        return Abp::$db->execute($sql);
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return $this
     */
    public function insert($attributes = [], $values = [])
    {
        if (empty($attributes)) {
            return $this;
        }
        if (empty($values)) {
            return $this;
        }
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }
        if (!is_array($values)) {
            $attributes = [$values];
        }
        $attributes = implode(', ', array_map(function ($attribute) {return "`$attribute`";}, $attributes));
        $values = implode(', ', array_map(function ($value) {return "'$value'";}, $values));

        $sql = "INSERT INTO `{$this->_tableName}` ($attributes) VALUES ($values)";
        return $this->commandExec($sql);
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function select($attributes = [])
    {
        if (!empty($this->_select)) {
            return $this;
        }
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }
        $attributes = implode(', ', array_map(function ($attribute) {return "`$attribute`";}, $attributes));
        if (empty($attributes)) {
            $attributes = '*';
        }
        $this->_select = "SELECT $attributes FROM `" . $this->_tableName . '`';
        return $this;
    }

    /**
     * @param string $column
     * @param string $value
     * @param string $contidion
     * @return $this
     */
    public function where($column, $value, $contidion = '=')
    {
        if (empty($this->_where)) {
            $this->_where = " WHERE `$column` $contidion '$value'";
        } else {
            $this->_where .= " AND `$column` $contidion '$value'";
        }
        return $this;
    }

    /**
     * @param string $column
     * @param string $value
     * @param string $contidion
     * @return $this
     */
    public function orWhere($column, $value, $contidion = '=')
    {
        if (empty($this->_where)) {
            $this->_where = " WHERE `$column` $contidion '$value'";
        } else {
            $this->_where .= " OR `$column` $contidion '$value'";
        }
        return $this;
    }

    /**
     * @param string $column
     * @param bool $sortTop
     * @return $this
     */
    public function order($column, $sortTop = true)
    {
        if ($sortTop) {
            $sort = 'ASC';
        } else {
            $sort = 'DESC';
        }
        if (empty($this->_order)) {
            $this->_order = " ORDER BY `$column` $sort";
        } else {
            $this->_order .= ", `$column` $sort";
        }

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function limit($value)
    {
        $this->_limit = " LIMIT $value";

        return $this;
    }

    public function describe()
    {
        return $this->command('DESCRIBE ' . $this->_tableName);
    }
    /**
     * @return array|bool|null
     */
    public function one()
    {
        if ($this->_tableName === null) {
            return null;
        }
        $this->select()->limit(1);
        $data =  $this->command();
        if (!$data) {
            return false;
        }
        return $data[0];
    }

    /**
     * @return array|bool|null
     */
    public function all()
    {
        if ($this->_tableName === null) {
            return null;
        }

        $this->select();
        $data =  $this->command();
        if (!$data) {
            return false;
        }
        return $data;
    }

    private function buildSql()
    {
        $sql = $this->_select . $this->_where . $this->_order . $this->_limit . ';';
        return $sql;
    }
}