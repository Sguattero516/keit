<?php
class Migration_20170529074640_AddClickParentSubId extends Migration 
{
    const DESCRIPTION_RU = 'Добавление таблицы click_links';

    const DESCRIPTION_EN = 'Create table click_links';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}click_links( 
                `id` bigint(20) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `sub_id` VARCHAR(255) NOT NULL,
                `parent_sub_id` VARCHAR(255) NOT NULL,
                UNIQUE INDEX `sub_ids` (`sub_id`, `parent_sub_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        self::execute($sql);
    }
}