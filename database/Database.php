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
    private $pdo;
    protected $config;
    protected static $instance = null;

    /**
     * Database constructor.
     * @throws DatabaseException
     */
    private function __construct()
    {
        $this->config = Abp::$config['db'];
        $dsn = 'mysql:host='.$this->config['host'].';dbname='.$this->config['name'].';';
        if (isset($this->config['port'])) {
            $dsn .= 'port=' . $this->config['port'].';';
        }
        if (isset($this->config['charset'])) {
            $dsn .= 'charset='.$this->config['charset'].';';
        }
        $user = $this->config['user'];
        $pass = $this->config['pass'];

        try {
            $this->pdo = new \PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $e) {
            $this->showException($e);
        }
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @throws DatabaseException
     */
    public function showException(\Throwable $e, string $sql = null): void
    {
        if ($sql === null) {
            throw $e;
        }
        throw new DatabaseException($e->getMessage(), $sql);
    }

    public static function instance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string|array $parametrs
     * @return array|bool
     * @throws DatabaseException
     */
    private function exec(string $sql, $parametrs, bool $return = false)
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
     * @param string|array $parametrs
     * @return array|bool
     */
    public function execute(string $sql , $parametrs = '')
    {
        return $this->exec($sql, $parametrs);
    }

    /**
     * @return array|bool
     * @throws DatabaseException
     */
    public function query(string $sql , string $parametrs = '')
    {
        return $this->exec($sql, $parametrs, true);
    }

    public function lastInsertId(): string
    {
        return $this->getPdo()->lastInsertId();
    }

    /**
     * @return bool|string
     */
    public function quote(string $sql)
    {
        return $this->getPdo()->quote($sql);
    }

    public function beginTransaction(): bool
    {
        return $this->getPdo()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getPdo()->commit();
    }

    public function rollBack(): bool
    {
        return $this->getPdo()->rollBack();
    }
}



