<?php
class Migration_20180104120559_AddFavouriteToStreams extends Migration 
{
    const DESCRIPTION_RU = 'Создание favourite_streams';

    const DESCRIPTION_EN = 'Create favourite_streams';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}favourite_streams(
           `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id INT unsigned  NOT NULL,
            stream_id INT unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY user_id_stream_id (user_id, stream_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        self::execute($sql);
    }
}