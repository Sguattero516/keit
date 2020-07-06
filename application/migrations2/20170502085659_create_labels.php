<?php
class Migration_20170502085659_CreateLabels extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы labels';

    const DESCRIPTION_EN = 'Create table labels';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}labels ( 
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            campaign_id int(12) unsigned,
            label_name varchar(50) NOT NULL,
            ref_name varchar(100) NOT NULL,
            ref_id int(12) unsigned NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY campaign_label_ref (campaign_id, ref_name, ref_id),
            KEY label_name(label_name)
        )";
        self::execute($sql);
    }
}