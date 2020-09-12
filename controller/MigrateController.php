<?php

namespace abp\controller;

use abp\core\Router;
use abp\controller\ConsoleController;
use Abp;
use abp\exception\MigrateException;
use abp\exception\NotFoundException;
use abp\model\Migrate;
use Phpfastcache\Helper\Psr16Adapter;

class MigrateController extends ConsoleController
{
    const MIGRATE_PREFIX = 'm';
    const TABLE_NAME = 'migrate';

    /**
     * @return string
     */
    public function createAction()
    {
        if (!isset($this->params[0])) {
            return 'Задайте имя миграции.';
        }
        $migrationName = $this->params[0];
        $date = date('ymd_His');
        $fullName = Abp::$root . Router::MIGRATE_FOLDER . '/'. self::MIGRATE_PREFIX . "_{$date}_$migrationName.sql";
        $fp = fopen($fullName, "w");
        fclose($fp);
    }

    /**
     * @return string
     * @throws MigrateException
     */
    public function upAction()
    {
        $migrations = scandir(Abp::$root . Router::MIGRATE_FOLDER);
        $migrationsNormal = [];
        $migrateTable = Abp::$db->query('SHOW TABLES LIKE ?', self::TABLE_NAME);
        if (!empty($migrateTable)) {
            $migrationDb = Migrate::find()->all();
            foreach ($migrationDb as $migrate) {
                $index = array_search($migrate->name, $migrations);
                if ($index !== false) {
                    unset($migrations[$index]);
                }
            }
        }

        foreach ($migrations as $migration) {
            $partMigration = explode('_', $migration);
            if ($partMigration[0] !== self::MIGRATE_PREFIX) {
                continue;
            }
            $migrationsNormal[] = $migration;
        }

        if (empty($migrationsNormal)) {
            return 'Нет миграций, доступных для применения';
        }
        $this->_print('Миграции, доступные для применения: ');
        foreach ($migrationsNormal as $migration) {
            $this->_print('   ' . $migration);
        }
        if ($this->isInteraction) {
            $input = readline('Применить миграции? (y/n) ');
        } else {
            $input = 'y';
        }
        if ($input !== 'y') {
            return 'Миграции не были применены.';
        }

        foreach ($migrationsNormal as $migration) {
            $migrateRoute = Abp::$root . Router::MIGRATE_FOLDER . '/' . $migration;
            if (file_exists($migrateRoute)) {
                $migrationCode = file_get_contents($migrateRoute);
            } else {
                throw new NotFoundException("Migrate file $migrateRoute not exist");
            }
            try {
                $result = Abp::$db->execute($migrationCode);
            } catch (\Exception $e) {
                throw new MigrateException("Не удалось применить миграцию $migration" . PHP_EOL . $e->getMessage());
            }
            if (!$result) {
                $this->_print("Не удалось применить миграцию $migration.");
            }
            $migrate = new Migrate();
            $migrate->name = $migration;
            $migrate->time = time();
            $migrate->save();
            $this->_print("Миграция $migration успешно применена.");

        }
    }
}