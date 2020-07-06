<?php
namespace Traffic\Pipeline;

use Traffic\Response\Response;
use Traffic\Model\Campaign;
use Traffic\Model\Landing;
use Traffic\Model\Offer;
use Traffic\Model\BaseStream;
use Traffic\Request\ServerRequest;
use Traffic\Pipeline\Stage\StageException;
use Traffic\RawClick;
use Traffic\Tools\Tools;

class Payload
{
    /**
     * @var ServerRequest
     */
    private $_serverRequest;

    /**
     * @var BaseStream
     */
    private $_stream;

    /**
     * @var Campaign
     */
    private $_campaign;
    /**
     * @var Offer
     */
    private $_offer;
    /**
     * @var Landing
     */
    private $_landing;
    /**
     * @var Response
     */
    private $_response;
    /**
     * @var RawClick
     */
    private $_rawClick;
    private $_rawClicksToStore = [];

    private $_actionType;
    private $_actionPayload;
    private $_actionOptions;
    // принудительно выполнить оффер
    private $_forcedOfferId;
    // принудительно выполнить кампанию
    private $_forcedCampaignId;
    // принудительно выполнить поток
    private $_forcedStreamId;
    // нужно ли создавать токен для оффера
    private $_tokenNeeded;
    // нужно ли добавлять token к URL
    private $_addTokenToUrl;
    // выполняет сразу редирект оффера, при false только token создает
    private $_forceRedirectOffer;
    // принудительно выбрать оффер, когда уже выбран лендинг. Deprecated начиная с KClickClient v3
    private $_forceChooseOffer;
    private $_aborted = false;

    private $_allowedOptions = ['server_request', 'campaign', 'stream', 'landing', 'offer', 'token',
        'response', 'raw_click', 'action_type', 'action_payload', 'action_options', 'raw_clicks_to_store',
        'forced_offer_id', 'token_needed', 'add_token_to_url', 'forced_campaign_id', 'forced_stream_id',
        'force_choose_offer', 'force_redirect_offer'
    ];

    public function __construct($options = [])
    {
        foreach ($options as $key => $value) {
            $name = '_' . Tools::toCamelCase($key, true);
            if (!in_array($key, $this->_allowedOptions)) {
                throw new StageException("Incorrect option {$key}");
            }
            $this->$name = $value;
        }
    }

    public function getServerRequest()
    {
        return $this->_serverRequest;
    }

    public function setServerRequest(ServerRequest $request)
    {
        if (empty($request)) {
            throw new \Exception('Trying to set empty ServerRequest');
        }
        return $this->_serverRequest = $request;
    }

    public function getRawClick()
    {
        return $this->_rawClick;
    }

    public function setRawClick(RawClick $rawClick)
    {
        return $this->_rawClick = $rawClick;
    }

    public function addRawClickToStore(RawClick $rawClick)
    {
        $this->_rawClicksToStore[] = $rawClick;
    }

    public function getRawClicksToStore()
    {
        return $this->_rawClicksToStore;
    }

    public function getCampaign()
    {
        return $this->_campaign;
    }

    public function setCampaign(Campaign $campaign = null)
    {
        $this->_campaign = $campaign;
    }
    public function getStream()
    {
        return $this->_stream;
    }

    public function setStream(BaseStream $stream = null)
    {
        $this->_stream = $stream;
    }

    public function getOffer()
    {
        return $this->_offer;
    }

    public function setOffer(Offer $offer = null)
    {
        $this->_offer = $offer;
    }

    public function getLanding()
    {
        return $this->_landing;
    }

    public function setLanding(Landing $landing = null)
    {
        $this->_landing = $landing;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function setResponse(Response $response)
    {
        if (empty($response)) {
            throw new \Exception('Trying to set empty Response');
        }

        $this->_response = $response;
    }

    public function getActionPayload()
    {
        return $this->_actionPayload;
    }

    public function setActionPayload($actionPayload = null)
    {
        $this->_actionPayload = $actionPayload;
    }

    public function getActionOptions()
    {
        return $this->_actionOptions;
    }

    public function getActionOption($key)
    {
        return isset($this->_actionOptions[$key]) ? $this->_actionOptions[$key] : null;
    }

    public function setActionOptions($actionOptions = null)
    {
        $this->_actionOptions = $actionOptions;
    }

    public function getActionType()
    {
        return $this->_actionType;
    }

    public function setActionType($actionType = null)
    {
        $this->_actionType = $actionType;
    }

    public function getForcedOfferId()
    {
        return $this->_forcedOfferId;
    }

    public function setOfferId($id)
    {
        $this->_forcedOfferId = $id;
    }
    public function isTokenNeeded()
    {
        return $this->_tokenNeeded;
    }

    public function setNeedToken($status = 1)
    {
        $this->_tokenNeeded = $status;
    }

    public function shouldAddTokenToURL()
    {
        return $this->_addTokenToUrl;
    }

    public function setAddTokenToUrl($status = 1)
    {
        $this->_addTokenToUrl = $status;
    }

    public function isAborted()
    {
        return $this->_aborted;
    }

    public function abort($status = true)
    {
        $this->_aborted = $status;
    }

    public function setForcedCampaignId($id)
    {
        $this->_forcedCampaignId = $id;
    }
    public function getForcedCampaignId()
    {
        return $this->_forcedCampaignId;
    }

    public function setForcedStreamId($id)
    {
        $this->_forcedStreamId = $id;
    }
    public function getForcedStreamId()
    {
        return $this->_forcedStreamId;
    }

    public function setForceRedirectOffer($state = true)
    {
        $this->_forceRedirectOffer = $state;
    }

    public function isForceRedirectOffer()
    {
        return $this->_forceRedirectOffer;
    }

    public function setForceChooseOffer($state = true)
    {
        $this->_forceChooseOffer = $state;
    }

    public function isForceChooseOffer()
    {
        return $this->_forceChooseOffer;
    }
}