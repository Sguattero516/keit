<?php
namespace Traffic\LpToken\Service;

use Traffic\Logging\Service\LoggerService;
use Traffic\LpToken\Storage\StorageInterface;
use Traffic\Model\Setting;
use Traffic\RawClick;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\AbstractService;
use Traffic\Settings\Repository\SettingsRepository;
use Traffic\Tools\Tools;

class LpTokenService extends AbstractService
{
    const UUID_PREFIX = 'uuid_';
    const DEFAULT_TTL = 86400;

    /**
     * @var StorageInterface
     */
    private $_storage;

    public function setStorage(StorageInterface $storage)
    {
        $this->_storage = $storage;
    }

    public static function generateUserKey($postFix = null)
    {
        return hash('sha256', SALT) . $postFix;
    }

    public function storeRawClick(RawClick $rawClick)
    {
        $token = $this->_generateToken($rawClick->getSubId());
        $encodedData = json_encode(Tools::utf8ize($rawClick->serialize()), JSON_PARTIAL_OUTPUT_ON_ERROR);

        if (json_last_error()) {
            LoggerService::instance()->error(function() use ($encodedData) {
                return '[LpTokenService] ' . json_last_error_msg() . ': ' .serialize($encodedData);
            });
        }

        $ttl = intval(CachedSettingsRepository::instance()->get(Setting::LP_OFFER_TOKEN_TTL, self::DEFAULT_TTL));
        if(!$ttl) {
            $ttl = self::DEFAULT_TTL;
        }
        $this->getStorage()->set($token, $encodedData, $ttl);
        return $token;
    }

    /**
     * @param $token
     * @return RawClick|null
     * @throws \Exception
     */
    public function getRawClickByToken($token)
    {
        $encodedData = $this->getStorage()->get($token);

        if (empty($encodedData)) {
            return null;
        }

        $data = json_decode($encodedData, JSON_OBJECT_AS_ARRAY);

        if (json_last_error()) {
            LoggerService::instance()->error('[LpTokenService] Can\'t decode json ' . $encodedData .' (' . json_last_error_msg() . ')');
        }
        return new RawClick($data);
    }

    public function delete($token)
    {
        $this->getStorage()->delete($token);
    }

    public function getStorage()
    {
        if (empty($this->_storage)) {
            throw new \Exception('Storage for LpTokenService is not set yet');
        }
        return $this->_storage;
    }

    private function _generateToken($subId)
    {
        return self::UUID_PREFIX . $subId . '_' . uniqid($subId, true);
    }

    public function subIdFromToken($token)
    {
        $arr = explode('_', $token);
        if (count($arr) > 2) {
            return $arr[1];
        }
        return NULL;
    }
}