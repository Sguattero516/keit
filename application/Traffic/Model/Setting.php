<?php
namespace Traffic\Model;

use Core\Model\AbstractModel;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Traffic\Service\SettingsService;

class Setting extends AbstractModel implements EntityModelInterface
{
    const IPDB = 'ipdb';
    const CHECK_BOT_UA = 'check_bot_ua';
    const GEODB = 'geodb';
    const DRAFT_DATA_STORAGE = 'draft_data_storage';
    const IMKLO_API_URL = 'imklo_api_url';
    const LP_DIR = 'lp_dir';
    const LP_ALLOW_PHP = 'lp_allow_php';
    const TRAFFIC_LOG_ENABLED = 'traffic_log_enabled';
    const CACHE_STORAGE = 'cache_storage';
    const AVOID_MYSQL = 'avoid_mysql';
    const LP_OFFER_TOKEN_TTL = 'lp_offer_token_ttl';
    const SECURE_TRANSPORT = 'secure_transport';
    const EXTRA_ACTION = 'extra_action';
    const EXTRA_CAMPAIGN = 'extra_campaign';

    const EXTRA_ACTION_PARAM_REDIRECT = 'redirect';
    const EXTRA_ACTION_PARAM_NOT_FOUND = 'not_found';
    const EXTRA_ACTION_PARAM_CAMPAIGN = 'campaign';

    protected static $_tableName = 'settings';

    protected static $_primaryKey = 'key';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'key' => Type::STRING,
        'value' => Type::STRING
    ];

    public static function service()
    {
        return SettingsService::instance();
    }

    public function getValue()
    {
        return $this->get('value');
    }
}
