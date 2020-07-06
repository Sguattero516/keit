<?php
class Migration_48 extends Migration {

    const DESCRIPTION_RU = 'Перенос данных из stream_groups в campaigns';

    const DESCRIPTION_EN = 'Move data from stream_groups to keitaro_campaigns';

    public static function up()
    {
        $sql = 'INSERT IGNORE INTO ' . self::getDb()->getPrefix() . 'campaigns (id, alias, name, `type`, cookies_ttl, action_type, action_payload,
            action_for_bots, bot_redirect_url, bot_text, action_tracking_disabled, position, `state`)
            SELECT id, `key`, name, `type`, uniq_time, exit_stream_type, exit_stream, action_for_bots, bot_redirect_url,
            bot_text, disable_tracking_exit, position, "active" FROM ' . self::getDb()->getPrefix() . 'stream_groups';
        self::execute($sql);

        $sql = 'UPDATE ' . self::getDb()->getPrefix() . 'campaigns SET mode = "banners" WHERE id IN
            (SELECT id FROM ' . self::getDb()->getPrefix() . 'stream_groups WHERE advertising_mode = 1)';
        self::execute($sql);
    }
}