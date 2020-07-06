<?php
class Migration_20200204141227_UpdateAppsFlyerIntegration extends Migration
{
    const DESCRIPTION_RU = 'Перенос интеграций AppsFlyer';

    const DESCRIPTION_EN = 'Move AppsFlyer integration';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "SELECT id, name, pull_api_options FROM {$prefix}affiliate_networks WHERE template_name = 'appsflyer'";
        foreach (self::execute($sql) as $integrationRow) {
            $data = json_decode($integrationRow['pull_api_options']);

            $newIntegrationData = [
                'integration' => 'appsflyer',
                'name' => $integrationRow['name'],
                'app_name' => $data->required_macro->app_name,
                'api_token' => $data->required_macro->api_token,
                'update_time' => $data->time_to_query,
                'proxy_enabled' => false,
                'proxy' => [],
            ];
            $jsonDat = json_encode($newIntegrationData);
            $date = date('Y-m-d H:i:s');
            $sqlFindIntegration = "SELECT id FROM {$prefix}third_party_integration WHERE integration = 'appsflyer' AND settings LIKE '%{$integrationRow['name']}%'";
            $row = self::getDb()->getRow($sqlFindIntegration);
            if (!$row) {
                $sql = "INSERT INTO {$prefix}third_party_integration (integration, settings, created_at, updated_at) VALUES 
                    ('appsflyer', '{$jsonDat}', '{$date}', '{$date}')";
                self::execute($sql);
            }
        }
    }
}
