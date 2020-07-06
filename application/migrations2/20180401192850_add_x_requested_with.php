<?php
class Migration_20180401192850_AddXRequestedWith extends Migration
{
    const DESCRIPTION_RU = 'Добавление x_requested_with в clicks';

    const DESCRIPTION_EN = 'Add x_requested_with to clicks';

    public static function up($rerun = false)
    {
        if ($rerun) {
            self::addColumnsIfNotExist(
                'clicks',
                [
                    'x_requested_with_id' => 'int(10) unsigned DEFAULT NULL',
                ],
                true
            );
            $prefix = self::getDb()->getPrefix();

            self::addColumnsIfNotExist(
                'conversions_2',
                [
                    'x_requested_with_id' => 'int(10) unsigned DEFAULT NULL',
                ],
                true
            );
            $prefix = self::getDb()->getPrefix();

            $sql = 'CREATE TABLE IF NOT EXISTS `' . $prefix . 'ref_x_requested_with` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `value` varchar(255) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `value` (`value`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
            self::execute($sql);
        }
    }
}