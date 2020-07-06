<?php
namespace Traffic\Settings\Repository;

use Core\Db\DataRepository;
use Core\Db\Db;
use Core\Entity\Model\EntityModelInterface;
use GuzzleHttp\Psr7\Uri;
use Traffic\Model\Setting;
use Traffic\Repository\AbstractBaseRepository;
use Traffic\Service\UrlService;

class SettingsRepository extends AbstractBaseRepository
{
    const NEW_STYLE = 'new';
    const OLD_STYLE = 'old';

    /**
     * @param $key
     * @return EntityModelInterface|Setting
     * @throws \Exception
     */
    public function findByKey($key)
    {
        $where = "`key` = " . Db::quote($key);
        return DataRepository::instance()->findFirst(Setting::definition(), $where);
    }

    /**
     * @param string|null $only
     * @return array Хэш [setting.key => setting.value]
     * @throws \Exception
     */
    public function allAsHash($only = null)
    {
        $settings = [];
        if ($only) {
            if (!is_array($only)) {
                $only = [$only];
            }
            $where = '`key` IN (' . implode(',', Db::quote($only)) .')';
        } else {
            $where = null;
        }

        $rows = DataRepository::instance()->rawRows(Setting::definition(), null, $where);

        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }

    public function getLinkFormats(Uri $uri)
    {
        return [
            ['value' => self::OLD_STYLE, 'name' => $this->_generateLink($uri, 'alias', self::OLD_STYLE)],
            ['value' => self::NEW_STYLE, 'name' => $this->_generateLink($uri, 'alias', self::NEW_STYLE)]
        ];
    }

    private function _generateLink(Uri $uri, $page, $style)
    {
        $str = UrlService::instance()->getBaseUrl($uri) . '/';
        if ($style == self::OLD_STYLE) {
            $str .= '?';
        }

        $str .= $page;
        return $str;
    }
}