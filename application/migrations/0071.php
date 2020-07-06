<?php
class Migration_71 extends Migration {

    const DESCRIPTION_RU = 'Добавление настроек псевдонимов';

    const DESCRIPTION_EN = 'Add aliases settings';

    public static function up()
    {
        $settings = array(
            'id' => 'g, group, sid',
            'keyword' => 'utm_term',
            'referrer' => 'referer',
            'se_referrer' => 'se_referer, seoref',
            'se' => '',
            'source' => 'utm_source',
            'sub_id_1' => 'subid1, utm_campaign',
            'sub_id_2' => 'subid2, utm_content',
            'sub_id_3' => 'subid3, utm_medium',
            'sub_id_4' => 'subid4',
            'currency' => 'USD',
            'draft_data_storage' => 'db'
        );

        foreach ($settings as $name => $value) {
            $sql = "INSERT IGNORE " . self::getDb()->getPrefix() . 'settings (`key`, `value`) VALUES
            ("' . $name. '_aliases", "' . $value . '")';
            self::execute($sql);
        }

    }
}