<?php
class Migration_20161105092911_AddSchemaLandingOfferToStreams extends Migration 
{
    const DESCRIPTION_RU = 'Добавление schema, landing_id, offer_id в streams';

    const DESCRIPTION_EN = 'Add schema, landing_id, offer_id to streams';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE {$prefix}streams 
            ADD `schema` VARCHAR(30) DEFAULT NULL AFTER `action_payload`,
            ADD `landing_id` INT(20) UNSIGNED DEFAULT NULL AFTER `schema`,
            ADD `offer_id` INT(20) UNSIGNED DEFAULT NULL AFTER `landing_id`
        ";

        self::silentExecute($sql);
    }
}