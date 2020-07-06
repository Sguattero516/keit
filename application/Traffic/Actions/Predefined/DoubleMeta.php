<?php
namespace Traffic\Actions\Predefined;

use Firebase\JWT\JWT;
use Traffic\Actions\AbstractAction;
use Traffic\Actions\Service\RedirectService;
use Traffic\LpToken\Service\LpTokenService;
use Traffic\Service\UrlService;

class DoubleMeta extends AbstractAction
{
    protected $_weight = 3;

    protected function _execute()
    {
        $this->_executeInContext();
    }

    protected function _executeDefault()
    {
        $gatewayUrl = $this->_getGatewayUrl($this->getActionPayload());
        $code = RedirectService::instance()->metaRedirect($gatewayUrl);
        $this->setContent($code);
        $this->setDestinationInfo($this->getActionPayload());
    }

    protected function _executeForFrame()
    {
        $url = $this->_getGatewayUrl($this->getActionPayload());
        $this->setContent(RedirectService::instance()->frameRedirect($url));
    }

    protected function _executeForScript()
    {
        $this->setContentType('application/javascript');
        $url = $this->_getGatewayUrl($this->getActionPayload());
        $this->setContent(RedirectService::instance()->scriptRedirect($url));
    }

    private function _getGatewayUrl($url)
    {
        $token = $this->_getToken($url);
        return $this->_getGatewayBaseUrl() . '?frm=dm&token=' . $token;
    }

    private function _getToken($url)
    {
        $token = array('url' => $url);
        return JWT::encode($token, LpTokenService::generateUserKey($this->getRawClick()->getUserAgent()));
    }

    private function _getGatewayBaseUrl()
    {
        $url = UrlService::instance()->getBaseUrl($this->getServerRequest()->getUri(), 1);
        $campaign = $this->getCampaign();
        if ($campaign) {
            $url = preg_replace('/'. preg_quote('/' . $campaign->getAlias(), '/') . '$/', '', $url);
        }
        $url .= '/gateway.php';
        return $url;
    }
}
