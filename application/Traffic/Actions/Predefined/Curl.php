<?php
namespace Traffic\Actions\Predefined;

use GuzzleHttp\Exception\RequestException;
use Traffic\Actions\AbstractAction;
use Traffic\Actions\CurlService;
use Traffic\Logging\Service\LoggerService;
use Traffic\Request\ServerRequest;
use Traffic\Service\ConfigService;
use Traffic\Tools\Tools;

class Curl extends AbstractAction
{
    protected $_weight = 3;

    const TIMEOUT = 10;

    public function getType()
    {
        return self::TYPE_REDIRECT;
    }

    protected function _execute()
	{
        $url = trim($this->getActionPayload());

        if (ConfigService::instance()->isDemo()) {
            return "Here would be content of " . $url;
        }

        $opts = [
            'localDomain' => $this->getServerRequest()->getHeaderLine(ServerRequest::HEADER_HOST),
            'url' => $url,
            'user_agent' => $this->getRawClick()->getUserAgent(),
            'referrer' => $this->getPipelinePayload()->getActionOption('referrer')
        ];

        $result = CurlService::instance()->request($opts);

        if (!empty($result['content_type'])) {
            $this->setContentType($result['content_type']);
        }

        if (!empty($result['error'])) {
            $this->setContent('Oops! Something went wrong on the requesting page');
            return null;
        }

        if (!empty($result['body'])) {
            $contentType = $result['content_type'];
            $body = $result['body'];
            if (strstr($contentType, 'image') || strstr($contentType, 'application/pdf')) {
                $content = base64_encode($body);
            } else {
                $content = $this->processMacros($result['body']);
            }

            // fix bad utf-8
            $content = Tools::utf8ize($content);
            $this->setContent($content);
            $this->setDestinationInfo('CURL: ' . $url);
        }
        return null;
	}
}
