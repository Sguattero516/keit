<?php
use Core\Model\Shard;
use Services\ShardService;


class Migration_16 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки sub_id и sub_id_2';

    const DESCRIPTION_EN = 'Add fields sub_id and sub_id_2';

    public static function up()
    {
        $shards = ShardService::instance()->getShardsFromDb();
        foreach ($shards as $shard) {
            $sql = 'ALTER TABLE `' . self::getDb()->getPrefix() . 'stats_'.$shard->getDate().'` ADD `sub_id` VARCHAR( 25 ) NULL DEFAULT NULL,
            ADD `sub_id_2` VARCHAR( 100 ) NULL DEFAULT NULL';
            self::silentExecute($sql);
        }
    }
}