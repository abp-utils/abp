<?php

namespace abp\model;

use abp\component\Security;
use abp\database\ActiveQuery;

class UserSession extends ActiveQuery
{
    private static $tableName;

    public function __construct()
    {
        parent::__construct(self::class);
    }

    public static function tableName()
    {
        return self::$tableName;
    }

    public static function setTableName(string $tableName)
    {
        self::$tableName = $tableName;
    }
}