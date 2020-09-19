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
            return 'The migration name cannot be empty.';
        }
        $migrationName = $this->params[0];
        $date = date('ymd_His');
        $migrateName = self::MIGRATE_PREFIX . "_{$date}_$migrationName.sql";
        $fullName = Abp::$root . Router::MIGRATE_FOLDER . '/'. $migrateName;
        if (!$this->askYNQuestion(
            "To create a migration \"$migrateName\" ? (yes|no) [no]: "
        )) {
            return 'Migration creation was refused.';
        }
        $fp = fopen($fullName, "w");
        fclose($fp);
        return "Migration \"$migrateName\" was created.";
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
            return 'There is no migration available for the application.';
        }
        $this->_print('Migration available for the application: ');
        foreach ($migrationsNormal as $migration) {
            $this->_print('   ' . $migration);
        }
        if (!$this->askYNQuestion(
            "To apply the migration? (yes|no) [no]: "
        )) {
            return 'Migrations were not applied.';
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
            } catch (\Throwable $e) {
                throw new MigrateException("Failed to apply migration $migration" . PHP_EOL . $e->getMessage());
            }
            if (!$result) {
                $this->_print("Failed to apply migration $migration.");
            }
            $migrate = new Migrate();
            $migrate->name = $migration;
            $migrate->time = time();
            $migrate->save();
            $this->_print("Migration $migration successfully applied.");

        }
    }
}