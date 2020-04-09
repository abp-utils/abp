<?php

namespace abp\database;

use abp\component\StringHelper;
use abp\core\Model;
use Abp;

/**
 * Class Query
 * @package app\database
 *
 * @property string $_update;
 * @property string $_select;
 * @property string $_where;
 * @property string $_order;
 * @property string $_limit;
 * @property string $_join;
 *
 * @property bool $_whereIsNull
 */
class Query extends Model
{
    const FieldPrimaryKey = 'PRI';

    const JOIN_INNER = 'INNER';
    const JOIN_LEFT = 'LEFT';

    public $modelClass;

    protected $_update = '';
    protected $_select = '';
    protected $_where = '';
    protected $_order = '';
    protected $_limit = '';
    protected $_join = '';

    protected $_whereIsNull = false;

    /**
     * Query constructor.
     * @param string $class
     * @param array $params
     */
    public function __construct($class, $params = [])
    {
        $this->modelClass = $class;
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
        if (count($attributes) !== count($values)) {
            throw new \InvalidArgumentException('Количество столбцов должно быть равно количеству значений.');
        }

        $attributes = implode(', ', array_map(function ($attribute) {return "`$attribute`";}, $attributes));
        $values = implode(', ', array_map(function ($value) {
            if (is_string($value)) {
                return '\'' . str_replace('\'', '\\\'' , $value) . '\'';
            }
            return $value;
            }, $values)
        );

        $sql = "INSERT INTO `{$this->_tableName}` ($attributes) VALUES ($values);";
        $result = $this->commandExec($sql);
        if (!$result) {
            return false;
        }
        return Abp::$db->lastInsertId();
    }

    /**
     * @param array $attributes
     * @param array $values
     * @return $this
     */
    public function update($attributes = [], $values = [])
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
        if (count($attributes) !== count($values)) {
            throw new \InvalidArgumentException('Количество столбцов должно быть равно количеству значений.');
        }
        $parametrs = '';
        foreach ($attributes as $key => $attribute) {
            if (is_string($values[$key])) {
                $param = "'{$values[$key]}'";
            } else {
                $param = $values[$key];
            }
            $parametrs .= "`$attribute` = $param, ";
        }

        $parametrs = substr($parametrs, 0 ,-2);
        $this->_update = "UPDATE `{$this->_tableName}` SET $parametrs";

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function select($attributes = [], $quotes = true, $addSelect = false)
    {
        if (!$addSelect && !empty($this->_select)) {
            return $this;
        }

        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        if ($quotes) {
            $attributes = implode(', ', array_map(function ($attribute) {return "`$attribute`";}, $attributes));
        } else {
            $attributes = implode(', ', $attributes);
        }

        if (empty($attributes)) {
            $attributes = '*';
        }

        if (!empty($this->_select)) {
            $this->_select .= ", $attributes";
        } else {
            $this->_select = "SELECT $attributes ";
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function selectFrom()
    {
        if (empty($this->_select) && empty($this->_update)) {
            $this->select();
        }

        if (!empty($this->_select)) {
            $this->_select .= " FROM `" . $this->_tableName . '`';
        }

        return $this;
    }

    /**
     * @param string|array $contidion
     * @param string $value
     * @param string $contidion
     * @return $this
     */
    public function where($expression, $value = null, $contidion = '=')
    {
        if (!is_array($expression)) {
            $column = $expression;
            if ($value === null) {
                throw new \InvalidArgumentException("Не задано значение для столбца $column.");
            }
        } else {
            foreach ($expression as $expressionKey => $expressionCalue) {
                $column = $expressionKey;
                $value = $expressionCalue;
            }
        }
        if (empty($this->_where)) {
            $this->_where = " WHERE {$this->_tableName}.$column $contidion '$value'";
        } else {
            $this->_where .= " AND {$this->_tableName}.$column $contidion '$value'";
        }
        if (!$this->_whereIsNull && $contidion == '<>') {
            $this->_where .= " OR `$column` is null";
            $this->_whereIsNull = true;
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
            $this->_where = " WHERE {$this->_tableName}.$column $contidion '$value'";
        } else {
            $this->_where .= " OR {$this->_tableName}.$column $contidion '$value'";
        }
        return $this;
    }

    /**
     * @param string $column
     * @param bool $sortTop
     * @return $this
     */
    public function order($column, $sortTop = true, $noReplace = true)
    {
        if ($sortTop) {
            $sort = 'ASC';
        } else {
            $sort = 'DESC';
        }
        if (empty($this->_order)) {
            $this->_order = " ORDER BY {$this->_tableName}.$column $sort";
        } else if ($noReplace) {
            $this->_order .= ", {$this->_tableName}.$column $sort";
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

    /**
     * @param string|null $table
     * @return array|bool
     */
    public function describe($table = null)
    {
        return $this->command('DESCRIBE ' . ($table ?? $this->_tableName));
    }

    /**
     * @param string $table
     * @param string $column1
     * @param string $column2
     * @param string $type
     * @return $this
     */
    private function join($table, $column1, $column2, $type)
    {
        $describeThisTable = $this->describe();
        $describeJoinTable = $this->describe($table);
        $attributes = [];
        foreach ($describeThisTable as $columnInfo) {
            $attributes[] = $this->_tableName . '.' . $columnInfo['Field'] . ' AS `' . $this->_tableName . '.' . $columnInfo['Field'] . '`';
        }
        foreach ($describeJoinTable as $columnInfo) {
            $attributes[] = $table . '.' . $columnInfo['Field'] . ' AS `' . $table . '.' . $columnInfo['Field'] . '`';
        }

        $this->select($attributes, false, true);

        $this->_join .= " $type JOIN `$table` ON {$this->_tableName}.$column1 = $table.$column2";

        $this->order($this->getPrimaryKey($describeThisTable), true, false);

        return $this;
    }

    /**
     * @param string $table
     * @param string $column1
     * @param string $column2
     * @return $this
     */
    public function leftJoin($table, $column1, $column2)
    {
        $this->join($table, $column1, $column2, self::JOIN_LEFT);
    }

    /**
     * @param string $table
     * @param string $column1
     * @param string $column2
     * @return $this
     */
    public function innerJoin($table, $column1, $column2)
    {
        $this->join($table, $column1, $column2, self::JOIN_INNER);
    }

    /**
     * @param array|null $describeTable
     * @return bool|string
     */
    public function getPrimaryKey($describeTable = null)
    {
        foreach ($describeTable as $field) {
            if ($field['Key'] == self::FieldPrimaryKey) {
                return $field['Field'];
            }
        }
        return false;
    }

    /**
     * @param array $relations
     */
    public function setRelations($relations)
    {
        foreach ($relations as $className => $relation) {
            if (count($relation) < 2) {
                return;
            }
            if (!isset($relation[2])) {
                $relation[2] = $relation[1];
            }

            $className =  StringHelper::conversionFilename($className);
            $this->leftJoin($className, $relation[1], $relation[2]);
        }

    }

    /**
     * @return array|bool|null
     */
    public function one()
    {
        if ($this->_tableName === null) {
            return null;
        }
        $this->setRelations($this->modelClass::relation());
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
        $this->setRelations($this->modelClass::relation());
        $this->select();
        $data =  $this->command();
        if (!$data) {
            return false;
        }
        return $data;
    }

    /**
     * @return string
     */
    public function buildSql()
    {
        $this->selectFrom();
        $sql = $this->_select . $this->_join . $this->_update . $this->_where . $this->_order . $this->_limit . ';';
        return $sql;
    }

}



