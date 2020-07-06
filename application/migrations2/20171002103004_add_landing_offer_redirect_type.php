<?php
class Migration_20171002103004_AddLandingOfferRedirectType extends Migration 
{
    const DESCRIPTION_RU = 'Добавление action_type к landings и offers';
    
    const DESCRIPTION_EN = 'Add action_type to landings and offers';

    public static function up($rerun = false)
    {
        $prefix = self::getPrefix();
        try {
            $sql = "ALTER IGNORE TABLE {$prefix}landings 
	                ADD `action_payload` TEXT NOT NULL,
                    ADD `action_options` TEXT NULL DEFAULT NULL,
                    ADD `action_type` VARCHAR(50) NULL DEFAULT NULL";
            self::execute($sql);
        } catch (ADODB_Exception $e) {
            $sql = "ALTER TABLE {$prefix}landings 
                    ADD `action_payload` TEXT NOT NULL,
                    ADD `action_options` TEXT NULL DEFAULT NULL,
                    ADD `action_type` VARCHAR(50) NULL DEFAULT NULL";
            self::silentExecute($sql);
        }

        $sql = "UPDATE {$prefix}landings SET action_payload = url WHERE action_payload = '' OR action_payload IS NULL";
        self::execute($sql);

        try {
            $sql = "ALTER IGNORE TABLE {$prefix}offers 
                    ADD `url` `action_payload` TEXT NOT NULL,
                    ADD `action_options` TEXT NULL DEFAULT NULL,
                    ADD `use_stream_action` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                    ADD `action_type` VARCHAR(50) NULL DEFAULT NULL";
            self::execute($sql);
        } catch (ADODB_Exception $e) {
            $sql = "ALTER TABLE {$prefix}offers 
                    ADD `action_payload` TEXT NOT NULL,
                    ADD `action_options` TEXT NULL DEFAULT NULL,
                    ADD `use_stream_action` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                    ADD `action_type` VARCHAR(50) NULL DEFAULT NULL";
            self::silentExecute($sql);
        }

        $sql = "UPDATE {$prefix}offers SET action_payload = url WHERE action_payload = '' OR action_payload IS NULL";
        self::execute($sql);
    }
}