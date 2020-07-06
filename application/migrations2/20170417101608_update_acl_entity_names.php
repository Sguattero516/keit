<?php
class Migration_20170417101608_UpdateAclEntityNames extends Migration 
{
    const DESCRIPTION_RU = 'Обновление имен сущностей в таблице acl';

    const DESCRIPTION_EN = 'Update entity names in acl table';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}acl SET `entity_type` = 'campaigns' WHERE `entity_type` = 'campaign'";
        self::execute($sql);
        $sql = "UPDATE {$prefix}acl SET `entity_type` = 'landings' WHERE `entity_type` = 'landing'";
        self::execute($sql);
        $sql = "UPDATE {$prefix}acl SET `entity_type` = 'offers' WHERE `entity_type` = 'offer'";
        self::execute($sql);
        $sql = "UPDATE {$prefix}acl SET `entity_type` = 'affiliate_networks' WHERE `entity_type` = 'affiliate_network'";
        self::execute($sql);
        $sql = "UPDATE {$prefix}acl SET `entity_type` = 'traffic_sources' WHERE `entity_type` = 'traffic_source'";
        self::execute($sql);
    }
}