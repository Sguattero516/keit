<?php
class Migration_20190125105401_RemoveAntibot extends Migration 
{
    const DESCRIPTION_RU = 'Удаление потоков antibot';

    const DESCRIPTION_EN = 'Remove antibot streams';

    public static function up($rerun = false)
    {
        if ($rerun) {
            $prefix = self::getPrefix();
            $sql = "UPDATE {$prefix}streams SET `state` = 'deleted' WHERE `name`='antibot';";
            self::execute($sql);
        }
    }
}