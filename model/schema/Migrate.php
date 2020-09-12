<?php

namespace abp\model\schema;

use abp\database\ActiveRecord;

/**
 * Class Migrate
 * @package abp\model\schema
 *
 * @property int $migrate_id
 * @property string $name
 * @property int $time
 */
class Migrate extends ActiveRecord
{
    public static function find()
    {
        return new \abp\model\query\Migrate(get_called_class());
    }
}