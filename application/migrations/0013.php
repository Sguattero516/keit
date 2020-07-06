<?php
use Core\Model\Shard;
use Services\ShardService;


class Migration_13 extends Migration {

    const DESCRIPTION_RU = 'Добавление поля region в таблицы статистики';

    const DESCRIPTION_EN = 'Add field region to report tables';

    public static function up()
    {
        $shards = ShardService::instance()->getShardsFromDb();
        foreach ($shards as $shard) {
            $sql = 'ALTER TABLE `' . self::getDb()->getPrefix() . 'stats_'.$shard->getDate().'` ADD `region` VARCHAR( 2 ) NULL DEFAULT NULL AFTER `city`';
            try {
                self::execute($sql);
            } catch (ADODB_Exception $e) {

            }
        }
    }
}