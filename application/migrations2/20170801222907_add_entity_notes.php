<?php
class Migration_20170801222907_AddEntityNotes extends Migration 
{
    const DESCRIPTION_RU = 'Добавление notes к affiliate_networks, landings, offers, campaigns и traffic_sources';

    const DESCRIPTION_EN = 'Add notes to affiliate_networks, landings, offers, campaigns and traffic_sources';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}affiliate_networks 
                ADD notes TEXT NULL DEFAULT NULL";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}landings 
                ADD notes TEXT NULL DEFAULT NULL";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}offers 
                ADD notes TEXT NULL DEFAULT NULL";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}campaigns 
                ADD notes TEXT NULL DEFAULT NULL";
        self::silentExecute($sql);
        $sql = "ALTER TABLE {$prefix}traffic_sources 
                ADD notes TEXT NULL DEFAULT NULL";
        self::silentExecute($sql);
    }
}