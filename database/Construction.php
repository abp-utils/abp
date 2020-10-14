<?php

namespace abp\database;

class Construction
{
    private const TYPES_REFLECTIONS = [
        Types::SQL_INT => Types::PHP_INT,
        Types::SQL_INTEGER => Types::PHP_INT,
        Types::SQL_CHAR => Types::PHP_STRING,
        Types::SQL_VARCHAR => Types::PHP_STRING,
    ];

    public static function getPhpTypeOnSqlType(string $sqlType): string
    {
        return self::TYPES_REFLECTIONS[$sqlType] ?? Types::PHP_STRING;
    }
}

