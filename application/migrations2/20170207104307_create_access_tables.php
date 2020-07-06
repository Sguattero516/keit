<?php
class Migration_20170207104307_CreateAccessTables extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы acl';

    const DESCRIPTION_EN = 'Create table acl';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE {$prefix}acl (
           id int(10) unsigned NOT NULL AUTO_INCREMENT,
           user_id int(10) unsigned NOT NULL,
           entity_type varchar(255) NOT NULL,
           access_type varchar(255) NOT NULL,
           groups TEXT NULL,
           entities TEXT NULL,
           PRIMARY KEY (id),
           KEY user_id (user_id),
           KEY entity_type (entity_type),
           KEY access_type (access_type)
        ) ENGINE=InnoDB";

        self::execute($sql);
    }
}