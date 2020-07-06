<?php
namespace Traffic\Model;

use Component\Clicks\Service\ClickService;
use Core\Model\AbstractModel;
use Core\Type\Type;
use Traffic\Repository\ParameterRepository;

class Click extends AbstractModel
{
    const EXTRA_PARAM_COUNT = 10;
    const SUB_ID_COUNT = 15;

    protected static $_tableName = 'clicks';

    protected static $_primaryKey = 'click_id';

    protected static $_fields = array(
        'click_id' => Type::INTEGER,
        'visitor_id' => Type::INTEGER,
        'sub_id' => Type::STRING,
        'landing_id' => Type::INTEGER,
        'ts_id' => Type::INTEGER,
        'offer_id' => Type::INTEGER,
        'affiliate_network_id' => Type::INTEGER,
        'datetime' => Type::DATETIME,

        'campaign_id' => Type::INTEGER,
        'parent_campaign_id' => Type::INTEGER,
        'stream_id' => Type::INTEGER,

        'destination_id' => Type::INTEGER,

        'is_unique_stream' => Type::BOOLEAN,
        'is_unique_campaign' => Type::BOOLEAN,
        'is_unique_global' => Type::BOOLEAN,
        'is_bot' => Type::BOOLEAN,
        'is_using_proxy' => Type::BOOLEAN,
        'is_empty_referrer' => Type::BOOLEAN,

        'search_engine_id' => Type::INTEGER,
        'source_id' => Type::INTEGER,
        'x_requested_with_id' => Type::INTEGER,
        'referrer_id' => Type::INTEGER,
        'keyword_id' => Type::INTEGER,
        'creative_id_id' => Type::INTEGER,
        'external_id_id' => Type::INTEGER,
        'ad_campaign_id_id' => Type::INTEGER,

        'sub_id_1_id' => Type::INTEGER,
        'sub_id_2_id' => Type::INTEGER,
        'sub_id_3_id' => Type::INTEGER,
        'sub_id_4_id' => Type::INTEGER,
        'sub_id_5_id' => Type::INTEGER,
        'sub_id_6_id' => Type::INTEGER,
        'sub_id_7_id' => Type::INTEGER,
        'sub_id_8_id' => Type::INTEGER,
        'sub_id_9_id' => Type::INTEGER,
        'sub_id_10_id' => Type::INTEGER,
        'sub_id_11_id' => Type::INTEGER,
        'sub_id_12_id' => Type::INTEGER,
        'sub_id_13_id' => Type::INTEGER,
        'sub_id_14_id' => Type::INTEGER,
        'sub_id_15_id' => Type::INTEGER,

        'extra_param_1' => Type::STRING,
        'extra_param_2' => Type::STRING,
        'extra_param_3' => Type::STRING,
        'extra_param_4' => Type::STRING,
        'extra_param_5' => Type::STRING,
        'extra_param_6' => Type::STRING,
        'extra_param_7' => Type::STRING,
        'extra_param_8' => Type::STRING,
        'extra_param_9' => Type::STRING,
        'extra_param_10' => Type::STRING,

        'lead_revenue' => Type::DECIMAL,
        'sale_revenue' => Type::DECIMAL,
        'rejected_revenue' => Type::DECIMAL,

        'cost' => Type::DECIMAL,
        'rebills' => Type::INTEGER,

        'is_lead' => Type::BOOLEAN,
        'is_sale' => Type::BOOLEAN,
        'is_rejected' => Type::BOOLEAN,

        'landing_clicked' => Type::BOOLEAN,
        'landing_clicked_datetime' => Type::DATETIME,
    );

    static public function getSubIdCount()
    {
        if (ParameterRepository::instance()->hasSubId15()) {
            return 15;
        } else {
            return 10;
        }
    }

    public static function service()
    {
        return ClickService::instance();
    }

    public function getSubId()
    {
        return $this->get('sub_id');
    }

    public function getStreamId()
    {
        return $this->get('stream_id');
    }

    public function getDateTime()
    {
        return $this->get('datetime');
    }

    public function lpClick($data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getCost()
    {
        return $this->get('cost');
    }

    public function addRevenue($revenueField, $revenue)
    {
        $newRevenue = $this->get($revenueField) + $revenue;
        $this->set($revenueField, $newRevenue);
        return $this;
    }

    public function subtractRevenue($revenueField, $revenue)
    {
        $newRevenue = $this->get($revenueField) - $revenue;
        if ($newRevenue < 0) {
            $newRevenue = 0;
        }
        $this->set($revenueField, $newRevenue);
        return $this;
    }

    public function getRevenue()
    {
        return $this->get('lead_revenue') + $this->get('sale_revenue') + $this->get('rejected_revenue');
    }

    public function isLead()
    {
        return $this->get('is_lead');
    }

    public function isSale()
    {
        return $this->get('is_sale');
    }

    public function isRejected()
    {
        return $this->get('is_rejected');
    }

    public function getOfferId()
    {
        return $this->get('offer_id');
    }

    public function getRebills()
    {
        return $this->get('rebills');
    }

    public function getLandingClicked()
    {
        return $this->get('landing_clicked');
    }

    public function getLandingClickedDatetime()
    {
        return $this->get('landing_clicked_datetime');
    }

    public function getLandingId()
    {
        return $this->get('landing_id');
    }

    public function getCampaignId()
    {
        return $this->get('campaign_id');
    }
}