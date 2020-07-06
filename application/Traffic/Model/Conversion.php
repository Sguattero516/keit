<?php
namespace Traffic\Model;

use Component\Conversions\Service\ConversionService;
use Component\Conversions\Validator\ConversionValidator;
use Core\Model\AbstractModel;
use Core\Type\Type;

class Conversion extends AbstractModel
{
    protected static $_tableName = 'conversions_2';

    protected static $_primaryKey = 'conversion_id';

    protected static $_fields = array(
        'conversion_id' => Type::INTEGER,
        'sub_id' => Type::STRING,
        'visitor_id' => Type::INTEGER,
        'click_id' => Type::INTEGER,

        'offer_id' => Type::INTEGER,
        'landing_id' => Type::INTEGER,
        'ts_id' => Type::INTEGER,
        'affiliate_network_id' => Type::INTEGER,

        'tid' => Type::STRING,
        'status' => Type::STRING,
        'previous_status' => Type::STRING,
        'original_status' => Type::STRING,

        'click_datetime' => Type::DATETIME,
        'postback_datetime' => Type::DATETIME,

        'campaign_id' => Type::INTEGER,
        'stream_id' => Type::INTEGER,

        'source_id' => Type::INTEGER,
        'x_requested_with_id' => Type::INTEGER,
        'referrer_id' => Type::INTEGER,
        'search_engine_id' => Type::INTEGER,
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

        'is_processed' => Type::BOOLEAN,
        'revenue' => Type::DECIMAL,
        'cost' => Type::DECIMAL,
        'params' => Type::JSON,
        'sale_datetime' => Type::DATETIME,
    );

    const LEAD = 'lead';
    const SALE = 'sale';
    const REJECTED = 'rejected';
    const REBILL = 'rebill';
    const IGNORE = 'ignore';

    public static function validator()
    {
        new ConversionValidator();
    }

    public static function service()
    {
        return ConversionService::instance();
    }

    public function isLead()
    {
        return $this->getStatus() === self::LEAD;
    }

    public function isSale()
    {
        return $this->getStatus() === self::SALE;
    }

    public function isRejected()
    {
        return $this->getStatus() === self::REJECTED;
    }

    public function isRebill()
    {
        return $this->getStatus() === self::REBILL;
    }

    public function isChangeToRejected()
    {
        return  $this->getStatus() === self::REJECTED &&
                $this->getPreviousStatus() !== self::REJECTED;
    }

    public function isChangeFromRejected()
    {
        return  $this->getStatus() !== self::REJECTED &&
                $this->getPreviousStatus() === self::REJECTED;
    }

    public function isPreviousLead()
    {
        return $this->getPreviousStatus() === self::LEAD;
    }

    public function isPreviousSale()
    {
        return $this->getPreviousStatus() === self::SALE;
    }

    public function isPreviousRejected()
    {
        return $this->getPreviousStatus() === self::REJECTED;
    }

    public function isPreviousRebill()
    {
        return $this->getPreviousStatus() === self::REBILL;
    }

    public function setCost($cost)
    {
        $this->set('cost', $cost);
        return $this;
    }

    public function getCost()
    {
        return $this->get('cost');
    }

    public function setRevenue($value)
    {
        $this->set('revenue', $value);
        return $this;
    }

    public function getRevenue()
    {
        return $this->get('revenue');
    }

    public function getProfit()
    {
        return $this->get('revenue') - $this->get('cost');
    }

    public function getParams()
    {
        return $this->get('params');
    }

    public function getTid()
    {
        return $this->get('tid');
    }

    public function getSubId()
    {
        return $this->get('sub_id');
    }

    public function getStatus()
    {
        return $this->get('status');
    }

    public function getPreviousStatus()
    {
        return $this->get('previous_status');
    }

    public function getCampaignId()
    {
        return $this->get('campaign_id');
    }

    public function getStreamId()
    {
        return $this->get('stream_id');
    }

    public function getClickDateTime()
    {
        return $this->get('click_datetime');
    }

    public function isProcessed()
    {
        return $this->get('is_processed');
    }

    public function isConvertedLead()
    {
        return $this->getPreviousStatus() == Conversion::LEAD && $this->getStatus() == Conversion::SALE;
    }

    public function getOfferId(Conversion $conversion)
    {
        return $this->get('offer_id');
    }

    public function setProcessed($value)
    {
        $this->set('is_processed', $value);
        return $this;
    }

    public function getOriginalStatus()
    {
        return $this->get('original_status');
    }

    /**
     * @return \DateTime|null
     */
    public function getPostbackDatetime()
    {
        return $this->get('postback_datetime');
    }

    public function getSaleDateTime()
    {
        return $this->get('sale_datetime');
    }
}