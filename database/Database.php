<?php

namespace abp\database;

use Abp;
use abp\exception\DatabaseException;

class Database
{
    protected $pdo;
    protected $config;
    protected static $instance = null;

    private function __construct()
    {
        $this->config = Abp::$config['db'];
        $dsn = 'mysql:host='.$this->config['host'].';dbname='.$this->config['name'].';charset='.$this->config['charset'];
        $user = $this->config['user'];
        $pass = $this->config['pass'];

        try {
            $this->pdo = new \PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->showException($e);
        }
    }

    public function showException(\PDOException $e)
    {
        switch ($this->config['debug']) {
            case 'show':
                throw new DatabaseException($e->getMessage());
                break;
            case 'log':
                //insert log error here
                exit();
                break;
            case 'none':
            default:
                exit('Произошла ошибка, повторите попытку позже');
                break;
        }
    }

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function exec($sql, $parametrs, $return = false) {
        $stmt = $this->pdo->prepare($sql);
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
        } catch (\PDOException $e) {
            $this->showException($e);
        }
        if (!$return) {
            return $result;
        }
        if (!$result) {
            return false;
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function execute($sql , $parametrs = '')
    {
        return $this->exec($sql, $parametrs);
    }

    public function query($sql , $parametrs = '')
    {
        return $this->exec($sql, $parametrs, true);
    }

    public static function lastInsertId()
    {
        return self::$db->lastInsertId();
    }

    public static function quote($sql)
    {
        return self::$db->quote($sql);
    }
}