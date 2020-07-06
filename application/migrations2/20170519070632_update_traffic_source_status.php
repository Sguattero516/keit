<?php
use Core\Db\Db;

class Migration_20170519070632_UpdateTrafficSourceStatus extends Migration 
{
    const DESCRIPTION_RU = 'Исправление статусов traffic_sources';

    const DESCRIPTION_EN = 'Fix statuses of traffic_sources';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}traffic_sources 
                CHANGE COLUMN postback_statuses postback_statuses varchar(255) DEFAULT '[\"sale\",\"lead\",\"rejected\",\"rebill\"]'";
        self::execute($sql);
        $sql = "SELECT * FROM {$prefix}traffic_sources";
        foreach (self::getDb()->execute($sql) as $row) {
            $statuses = [];
            if (!empty($row['postback_statuses'])) {
                $statuses = json_decode($row['postback_statuses'], true);
            }
            foreach ($statuses as &$status) {
                if ($status == 'sell') {
                    $status = 'sale';
                }
            }
            $rebill = 'rebill';
            if (!in_array($rebill, $statuses)) {
                $statuses[] = $rebill;
            }
            $sql = "UPDATE {$prefix}traffic_sources SET postback_statuses = " . 
                    Db::quote(json_encode($statuses)) . 
                    " WHERE id = " . Db::quote($row['id']);
            self::getDb()->execute($sql);
        }
    }
}