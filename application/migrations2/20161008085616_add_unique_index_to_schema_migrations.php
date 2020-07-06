<?php
class Migration_20161008085616_AddUniqueIndexToSchemaMigrations extends Migration 
{
    const DESCRIPTION_RU = 'Добавление индекса уникальности на schema_migrations';

    const DESCRIPTION_EN = 'Add unique index to schema_migrations';

    public static function up()
    {
        try {
            $sql = "ALTER IGNORE TABLE `schema_migrations` DROP INDEX  `name`, 
            ADD UNIQUE  `name` (`name`)";
            self::execute($sql);
        } catch (\Exception $e) {
            $sql = "ALTER TABLE `schema_migrations` DROP INDEX  `name`, 
            ADD UNIQUE  `name` (`name`)";
            self::execute($sql);
        }
    }
}