<?php
class Migration_20170204130131_CreateReportBookmarks extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы report_bookmarks';

    const DESCRIPTION_EN = 'Create table report_bookmarks';

    public static function up()
    {
        $prefix = self::getPrefix();

        $sql = "CREATE TABLE {$prefix}report_bookmarks (
          id int(10) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(50) NOT NULL, 
          user_id int(10) unsigned NOT NULL,
          is_shared tinyint(1) unsigned DEFAULT 0,
          payload text NOT NULL,
          PRIMARY KEY (id),
          KEY user_id (user_id)
        ) ENGINE=InnoDB";
        self::execute($sql);
    }
}