<?php
use Core\Db\Db;

class Migration_20170509185110_FixGroups extends Migration 
{
    const DESCRIPTION_RU = 'Конвертация groups в новый формат';

    const DESCRIPTION_EN = 'Convert groups to new format';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        Db::instance()->beginTransaction();
        $sql = "UPDATE {$prefix}groups SET type = " . 
                    Db::quote('campaigns') . 
                    "WHERE type = " . Db::quote('campaign');
        self::getDb()->execute($sql);;            
        $sql = "UPDATE {$prefix}groups SET type = " . 
                    Db::quote('landings') . 
                    "WHERE type = " . Db::quote('landing');
        self::getDb()->execute($sql);;            
        $sql = "UPDATE {$prefix}groups SET type = " . 
                    Db::quote('offers') . 
                    "WHERE type = " . Db::quote('offer');
        self::getDb()->execute($sql);
        Db::instance()->commit();
    }
}