<?php
class Migration_20161105102135_UpdateSchemaInStreams extends Migration 
{
    const DESCRIPTION_RU = 'Обновление schema в streams';

    const DESCRIPTION_EN = 'Update schema in streams';

    public static function up()
    {
        $prefix = static::getPrefix();

        foreach (static::getStreams() as $stream) {
            $schema = static::getSchema($stream['action_type']);
            $sql = "UPDATE {$prefix}streams SET `schema` = '{$schema}' WHERE id = {$stream['id']}";
            self::getDb()->execute($sql);
        }
    }

    private static function getStreams()
    {
        $prefix = static::getPrefix();
        $sql = "SELECT * FROM {$prefix}streams WHERE `schema` IS NULL ";
        return static::execute($sql);
    }

    private static function getSchema($actionType)
    {
        switch($actionType) {
            case 'sub_id':
            case 'frame':
            case 'iframe':
            case 'campaign':
            case 'stream':
            case 'stream_id':
            case 'status404':
            case 'html':
            case 'build_html':
            case 'echo':
            case 'text':
                return 'action';
            default:
                return 'redirect';
        }
    }
}