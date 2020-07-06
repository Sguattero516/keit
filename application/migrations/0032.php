<?php
class Migration_32 extends Migration {

    const DESCRIPTION_RU = 'Конвертация фильтров мобильного трафика в тип устройств';

    const DESCRIPTION_EN = 'Convert mobile filter to device types';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();

        $sql = "SELECT * FROM {$prefix}streams WHERE mobile_filter <> '' AND mobile_filter IS NOT NULL";
        $result = self::execute($sql)->getArray();
        $value = serialize(array('mobile', 'tablet'));
        foreach ($result as $item) {
            $sql = "UPDATE {$prefix}streams
            SET device_type_filter = '{$item['mobile_filter']}',
                device_type_filter_value = '{$value}'
            WHERE id = {$item['id']}";
            self::silentExecute($sql);
        }
    }
}