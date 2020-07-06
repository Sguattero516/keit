<?php
class Migration_20200220124125_DisableUseCFCountry extends Migration
{
    const DESCRIPTION_RU = 'Отключение CF-IPCountry (CloudFlare)';

    const DESCRIPTION_EN = 'Disable CF-IPCountry (CloudFlare)';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}settings SET `value`='' WHERE `key`='use_cf_country'";
        self::execute($sql);
    }
}
