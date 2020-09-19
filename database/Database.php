<?php

namespace abp\database;

use Abp;
use abp\exception\DatabaseException;

/**
 * Class Database
 * @package abp\database
 */
class Database
{
    protected $pdo;
    protected $config;
    protected static $instance = null;

    /**
     * Database constructor.
     * @throws DatabaseException
     */
    private function __construct()
    {
        $this->config = Abp::$config['db'];
        $dsn = 'mysql:host='.$this->config['host'].';dbname='.$this->config['name'].';charset='.$this->config['charset'];
        $user = $this->config['user'];
        $pass = $this->config['pass'];

        try {
            $this->pdo = new \PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $e) {
            $this->showException($e);
        }
    }

    /**
     * @return \PDO
     */
    public function getDb()
    {
        return $this->pdo;
    }

    /**
     * @throws DatabaseException
     */
    public function showException(\Throwable $e, string $sql = null)
    {
        if ($sql === null) {
            throw new DatabaseException($e->getMessage());
        }
        throw new DatabaseException($e->getMessage() . ' SQL: ' . $sql);
    }

    /**
     * @return Database|null
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $sql
     * @param string|array $parametrs
     * @param bool $return
     * @return array|bool
     * @throws DatabaseException
     */
    public function exec(string $sql, $parametrs, $return = false)
    {
        try {
            $stmt = $this->pdo->prepare($sql);
        } catch (\Throwable $e) {
            $this->showException($e, $sql);
        }
        $parametrsNew = null;
        if($parametrs == '') {
            $parametrsNew = null;
        } else if (!is_array($parametrs)) {
            $parametrsNew = [$parametrs];
        } else {
            $parametrsNew = $parametrs;
        }
        try {
            $result = $stmt->execute($parametrsNew);
        } catch (\Throwable $e) {
            $this->showException($e, $sql);
        }
        if (!$return) {
            return $result;
        }
        if (!$result) {
            return false;
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param string|array $parametrs
     * @return array|bool
     */
    public function execute($sql , $parametrs = '')
    {
        return $this->exec($sql, $parametrs);
    }

    /**
     * @param string $sql
     * @param string $parametrs
     * @return array|bool
     * @throws DatabaseException
     */
    public function query(string $sql , $parametrs = '')
    {
        return $this->exec($sql, $parametrs, true);
    }

    /**
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $sql
     * @return false|string
     */
    public function quote($sql)
    {
        return $this->pdo->quote($sql);
    }
}



