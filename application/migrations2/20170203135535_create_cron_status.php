<?php
class Migration_20170203135535_CreateCronStatus extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы cron_status';

    const DESCRIPTION_EN = 'Create table cron_status';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE {$prefix}cron_status (
           id int(10) unsigned NOT NULL AUTO_INCREMENT,
           task_name varchar(40) NOT NULL,
           executed_at datetime NOT NULL,
           PRIMARY KEY (id),
           UNIQUE KEY task_name (task_name)
           ) ENGINE=InnoDB
        ";

        self::execute($sql);
    }
}