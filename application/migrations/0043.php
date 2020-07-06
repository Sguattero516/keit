<?php


class Migration_43 extends Migration {

    const DESCRIPTION_RU = 'Удаление полей checker_* из streams';

    const DESCRIPTION_EN = 'Delete fields checker_* from streams';

    public static function up()
    {
        $fields = array('checker_active', 'checker_url','checker_regx','checker_action','checker_interval',
            'checker_source',	'checker_next_run', 'checker_action_done');

        foreach ($fields as $field) {
            $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'streams
                 DROP `' . $field . '`';
            self::silentExecute($sql);
        }
    }
}