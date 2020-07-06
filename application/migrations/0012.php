<?php


class Migration_12 extends Migration {

    const DESCRIPTION_RU = 'Добавление имен параметров в config.ini';

    const DESCRIPTION_EN = 'Add parameter names to config.ini';

    public static function up()
    {
        $rows = array();
        $rows[] = '; Parameter names';
        $rows[] = 'id_names = id, g, group, sid';
        $rows[] = 'keyword_names = keyword, keywords, tds-key, parameter';
        $rows[] = 'referrer_names = referer, referrer, sref, ref';
        $rows[] = 'search_engine_referrer_names = se_referer, se_referrer, seoref';
        $rows[] = 'search_engine_names = se';
        $rows[] = 'source_names = source';
        //$rows[] = 'sub_id_names = sub_id, sid, subid';

        self::_addToConfig($rows);
    }

    public static function _addToConfig($rows, $addSpace = true)
    {
        $text = implode("\n", $rows);

        if ($addSpace) {
            $text = "\n\n" . $text;
        }

        file_put_contents(ROOT . '/application/config/config.ini', $text, FILE_APPEND);
    }
}