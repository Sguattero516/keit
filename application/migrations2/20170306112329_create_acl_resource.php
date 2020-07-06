<?php


class Migration_20170306112329_CreateAclResource extends Migration
{
    const DESCRIPTION_RU = 'Создание таблицы acl_resources';

    const DESCRIPTION_EN = 'Create table acl_resources';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE {$prefix}acl_resources (
           id int(10) unsigned NOT NULL AUTO_INCREMENT,
           user_id int(10) unsigned NOT NULL,
           resources TEXT NULL,
           PRIMARY KEY (id),
           KEY user_id (user_id)
        ) ENGINE=InnoDB";

        self::execute($sql);
    }
}