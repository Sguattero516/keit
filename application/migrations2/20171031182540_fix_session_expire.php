<?php
class Migration_20171031182540_FixSessionExpire extends Migration 
{
    const DESCRIPTION_RU = 'Очистка не актуальных сессий';

    const DESCRIPTION_EN = 'Clear unused sessions';

    public static function up($rerun = false)
    {
        if ($rerun) {
            $prefix = self::getPrefix();
            $sql = "DELETE FROM {$prefix}sessions 
            WHERE expires_at > DATE_ADD(NOW(), INTERVAL 8 YEAR) ";
            self::execute($sql);
        }
    }
}