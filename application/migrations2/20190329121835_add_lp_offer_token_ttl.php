<?php
class Migration_20190329121835_AddLpOfferTokenTtl extends Migration
{
    const DESCRIPTION_RU = 'Добавление настройки lp_offer_token_ttl';

    const DESCRIPTION_EN = 'Add a setting lp_offer_token_ttl';

    public static function up()
    {
        $prefix = self::getPrefix();
        $value = 1440;
        $sql = "INSERT IGNORE INTO {$prefix}settings (`key`, `value`) values ('lp_offer_token_ttl', {$value})";
        self::execute($sql);
    }
}