<?php
namespace Traffic\Pipeline\Stage;

class SendToCampaignException extends \Exception
{
    private $_campaignId;

    public function setCampaignId($id)
    {
        $this->_campaignId = $id;
    }

    public function getCampaignId()
    {
        return $this->_campaignId;
    }
}