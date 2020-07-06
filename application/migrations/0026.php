<?php
use Core\Model\Shard;
use Services\ShardService;


class Migration_26 extends Migration {

    const DESCRIPTION_RU = 'Увеличение размера колонки region в stats';

    const DESCRIPTION_EN = 'Increase size of column region in stats';

    public static function up()
    {
        $shards = ShardService::instance()->getShardsFromDb();
        foreach ($shards as $shard) {
            $sql = 'ALTER TABLE `' . self::getDb()->getPrefix() . 'stats_'.$shard->getDate().'` CHANGE `region` `region` VARCHAR( 3 )
                CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';
            self::silentExecute($sql);
        }
    }
}