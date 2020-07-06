<?php
class Migration_20170224142540_AddIndexes extends Migration 
{
    const DESCRIPTION_RU = 'Добавление индексов в clicks и visitors';

    const DESCRIPTION_EN = 'Add indexes to clicks and visitors';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks 
          ADD INDEX keyword_id(keyword_id),
          ADD INDEX referrer_id(referrer_id),
          ADD INDEX ts_id(ts_id),
          ADD INDEX sub_id_1_id(sub_id_1_id),
          ADD INDEX sub_id_2_id(sub_id_2_id),
          ADD INDEX sub_id_3_id(sub_id_3_id),
          ADD INDEX sub_id_4_id(sub_id_4_id),
          ADD INDEX sub_id_5_id(sub_id_5_id),
          ADD INDEX sub_id_6_id(sub_id_6_id),
          ADD INDEX sub_id_7_id(sub_id_7_id),
          ADD INDEX sub_id_8_id(sub_id_8_id),
          ADD INDEX sub_id_9_id(sub_id_9_id),
          ADD INDEX sub_id_10_id(sub_id_10_id),
          ADD INDEX destination_id(destination_id),          
          ADD INDEX campaign_id(campaign_id),
          ADD INDEX visitor_id(visitor_id),
          ADD INDEX source_id(source_id)
          ";
        self::execute($sql);
    }
}