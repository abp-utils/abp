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
 */
class Query extends Model
{
    const FieldPrimaryKey = 'PRI';

    const JOIN_INNER = 'INNER';
    const JOIN_LEFT = 'LEFT';
    
    public $modelClass;

    private $_fromFlag = false;

    protected $_update = '';
    protected $_select = '';
    protected $_where = '';
    protected $_order = '';
    protected $_limit = '';
    protected $_join = '';

    private $describe;

    /**
     * Query constructor.
     * @param string $class
     * @param array $params
     */
    public function __construct($class = null, $params = [])
    {
        if ($class === null) {
            return;
        }
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
        if (!empty($this->_select) && !$this->_fromFlag) {
            $this->_select .= " FROM `" . $this->_tableName . '`';
            $this->_fromFlag = true;
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
        [$column, $value, $contidion] = $this->convertExpressionValue($expression, $value);
        if (empty($this->_where)) {
            $this->_where = ' WHERE' . $this->wherePrepare($column, $value, $contidion);
        } else {
            $this->_where .= $this->wherePrepare($column, $value, $contidion, 'AND');
        }

        return $this;
    }

    public function orWhere($expression, $value = null, $contidion = '='): self
    {
        [$column, $value, $contidion] = $this->convertExpressionValue($expression, $value);
        if (empty($this->_where)) {
            $this->_where = ' WHERE' . $this->wherePrepare($column, $value, $contidion);
        } else {
            $this->_where .= $this->wherePrepare($column, $value, $contidion, 'OR');
        }
        return $this;
    }

    private function convertExpressionValue($expression, $value = null): array
    {
        $contidion = '=';
        if (!is_array($expression)) {
            $column = $expression;
            if ($value === null) {
                throw new \InvalidArgumentException("Value for $column undefined.");
            }
            return [$column, $value, $contidion];
        }
        if (count($expression) === 1) {
            return [array_key_first($expression), array_shift($expression), $contidion];
        }
        return [$expression[0], $expression[1], $expression[2] ?? $contidion];
    }

    private function wherePrepare(string $column, string $value, string $contidion = '=', ?string $operator = null)
    {
        $sqlLPlus = '';
        $sqlRPlus = '';

        if ($contidion == '<>') {
            $sqlLPlus = '(';
            $sqlRPlus = " OR {$this->_tableName}.$column IS NULL)";
        }
        return " $operator $sqlLPlus{$this->_tableName}.$column $contidion '$value' $sqlRPlus";
    }

    /**
     * Condition exsample:
     * ['column1' => 'value'],
     * 'and'
     * ['column2', 'value'],
     * 'or'
     * ['column3', 'value', '>'],
     * 'and'
     * ['column4', 'value', '<'],
     * 'or'
     * ['column5', 'value', '<>']
     *
     * Attention: $whereOperator use ONLY FRAMEWORK!
     */
    public function whereContidion(array $contidion, $whereOperator = 'AND'): self
    {
        $contidionsCount = count($contidion);
        if ($contidionsCount % 2 === 0) {
            throw new \InvalidArgumentException('Uncorrect contidion.');
        }
        $indexContidionsCount = 0;
        $where = '';
        if (empty($this->_where)) {
            $where = 'WHERE (';
        } else {
            $where = "$whereOperator (";
        }
        $currentconditionSQL = null;
        $operator = null;
        while ($indexContidionsCount < $contidionsCount) {
            $currentExpression = $contidion[$indexContidionsCount];
            [$column, $value, $contidionValue] = $this->convertExpressionValue($currentExpression);
            $where .= $this->wherePrepare($column, $value, $contidionValue, $operator);
            $indexContidionsCount++;
            if ($indexContidionsCount === $contidionsCount) {
                break;
            }
            $operator = $contidion[$indexContidionsCount];
            $indexContidionsCount++;
        }
        $where .= ')';
        $this->_where .= $where;
        return $this;
    }

    public function orWhereContidion(array $contidion): self
    {
        return $this->whereContidion($contidion, 'OR');
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

    public function limit(int $value): self
    {
        $this->_limit = " LIMIT $value";

        return $this;
    }

    /**
     * @return array|bool
     */
    public function describe(?string $table = null)
    {
        if ($this->describe === null) {
            $this->describe = $this->command('DESCRIBE `' . ($table ?? $this->_tableName) . '`');
        }
        return $this->describe;
    }

    private function join(string $table, string $column1, string $column2, string $type): self
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

    public function leftJoin(string $table, string $column1, string $column2): self
    {
        $this->join($table, $column1, $column2, self::JOIN_LEFT);
    }

    public function innerJoin(string $table, string $column1, string $column2): self
    {
        $this->join($table, $column1, $column2, self::JOIN_INNER);
    }

    /**
     * @return bool|string
     */
    public function getPrimaryKey(?array $describeTable = null)
    {
        if ($describeTable === null) {
            $describeTable = $this->describe();
        }
        foreach ($describeTable as $field) {
            if ($field['Key'] == self::FieldPrimaryKey) {
                return $field['Field'];
            }
        }
        return false;
    }

    public function setRelations(array $relations): void
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
     * @return mixed|null
     */
    public function one()
    {
        if ($this->_tableName === null) {
            return null;
        }
        $data = $this->prepareFind();
        if (empty($data)) {
            return null;
        }
        return $data[0];
    }

    /**
     * @return array|bool
     */
    public function all()
    {
        if ($this->_tableName === null) {
            return [];
        }
        $data = $this->prepareFind();
        if (empty($data)) {
            return [];
        }
        return $data;
    }

    public function exist(): bool
    {
        if ($this->_tableName === null) {
            return false;
        }
        $sql = 'SELECT EXISTS(' . rtrim($this->buildSql(), ' ;') . ');';
        return (bool) array_shift($this->command($sql)[0]);
    }

    /**
     * @return array|bool
     */
    private function prepareFind()
    {
        $this->setRelations($this->modelClass::relation());
        $this->select();
        return $this->command();
    }

    public function buildSql(): string
    {
        $this->selectFrom();
        if (!empty($this->_update)) {
            $sql = $this->_update . $this->_where . $this->_order . $this->_limit . ';';
        } else {
            $sql = $this->_select . $this->_join . $this->_where . $this->_order . $this->_limit . ';';
        }
        $this->clearSql();
        return $sql;
    }

    public function clearSql(): void
    {
        $this->_fromFlag = false;

        $this->_select = '';
        $this->_join = '';
        $this->_update = '';
        $this->_where = '';
        $this->_order = '';
        $this->_limit= '';
    }
}



