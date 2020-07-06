<?php
class Migration_20200120101505_UpdateIntegrationsData extends Migration
{
    const DESCRIPTION_RU = 'Обновление данных в интеграциях';

    const DESCRIPTION_EN = 'Update integrations data';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}third_party_integration set settings=REPLACE(settings,'campaign','ad_account_id')";
        self::execute($sql);
        $sql = "UPDATE {$prefix}third_party_integration set settings=REPLACE(settings,'useProxy','proxy_enabled')";
        self::execute($sql);
        $sql = "UPDATE {$prefix}third_party_integration set settings=REPLACE(settings,'message','last_error')";
        self::execute($sql);
    }
}
