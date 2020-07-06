<?php
namespace Traffic\Session\Storage;

use Traffic\Model\Campaign;
use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Request\ServerRequest;
use Traffic\Response\Response;

interface StorageInterface
{
    public function __construct(ServerRequest $serverRequest);
    /**
     * @param ServerRequest $serverRequest
     * @param Response $response
     * @param RawClick $rawClick
     * @param Campaign $campaign
     * @param BaseStream|NULL $stream
     * @return ServerRequest[]|Response[]|null
     */
    public function save(ServerRequest $serverRequest, Response $response, RawClick $rawClick, Campaign $campaign, BaseStream $stream);
    public function isUniqueForCampaign(RawClick $rawClick, Campaign $campaign);
    public function isUniqueForStream(RawClick $rawClick, Campaign $campaign, BaseStream $stream);
    public function isUniqueGlobal(RawClick $rawClick, Campaign $campaign);
}