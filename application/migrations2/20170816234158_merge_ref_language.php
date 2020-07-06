<?php
use Core\Db\Db;

class Migration_20170816234158_MergeRefLanguage extends Migration 
{
    const DESCRIPTION_RU = 'Слияние дублей языков в статистике';

    const DESCRIPTION_EN = 'Merge language copies in statistics';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "select value, group_concat(id, '_', value) as valueArr
            from {$prefix}ref_languages 
            where value != ''
            group by value";
        $langs = self::getDb()->execute($sql);
        foreach ($langs as $langArr) {
            $langArr = explode(',', $langArr['valueArr']);
            $langDic = [];
            $foundCorrectId = NULL;
            foreach($langArr as $langArrVal) {
                $langArrVal = explode('_', $langArrVal);
                if (strtoupper($langArrVal[1]) === $langArrVal[1]) {
                    $foundCorrectId = $langArrVal[0];
                } else {
                    $langDic[$langArrVal[0]] = $langArrVal[1];
                }
            }
            if (count($langDic) <= 1 && $foundCorrectId) {
                //everything ok
                continue;
            }
            if (empty($foundCorrectId)) {
                $firstVal = array_values($langDic);
                $firstVal = $firstVal[0];
                $newVal = strtoupper($firstVal);
                $sql = "INSERT INTO {$prefix}ref_languages (value) VALUES ('{$newVal}')";
                self::execute($sql);
                $foundCorrectId = self::getDb()->getInsertId();
            }
            Db::instance()->beginTransaction();
            $ids = implode(',', array_keys($langDic));
            $sql = "DELETE FROM {$prefix}ref_languages WHERE id in ({$ids})";
            self::execute($sql);
            $sql = "UPDATE {$prefix}visitors SET language_id = {$foundCorrectId} WHERE language_id in ({$ids})";
            self::execute($sql);
            Db::instance()->commit();
        }
    }
}