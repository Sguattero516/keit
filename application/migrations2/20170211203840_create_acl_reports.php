<?php
class Migration_20170211203840_CreateAclReports extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы acl_reports';

    const DESCRIPTION_EN = 'Create table acl_reports';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE {$prefix}acl_reports (
           id int(10) unsigned NOT NULL AUTO_INCREMENT,
           user_id int(10) unsigned NOT NULL,
           columns TEXT NULL,
           PRIMARY KEY (id),
           KEY user_id (user_id)
        ) ENGINE=InnoDB";

        self::execute($sql);
    }
}