<?php
namespace Traffic\Macros;

use Core\Sandbox\SandboxContext;
use Traffic\BackCompatibility\BackCompatibility;
use Traffic\Logging\Service\LoggerService;

/**
 * Здесь проводится обработка макросов внутри текста, строки и html-кода.
 */
class MacrosProcessor
{
    public static function process(SandboxContext $pageContext, $content)
    {
        $processor = new MacrosProcessor();
        return $processor->processContent($content, $pageContext);
    }

    public function processContent($content, SandboxContext $pageContext)
    {
        if (empty($pageContext)) {
            throw new \Exception('pageContext is not defined');
        }
        // в содержимом макросов нет
        if (!strstr($content, '$') && !strstr($content, '{')) {
            return $content;
        }

        $params = $pageContext->serverRequest()->getAllParams();
        $params = $this->_addParamsFromCampaign($params, $pageContext);

        $parserItems = $this->_parseForMacros($content);

        foreach ($parserItems as $parserItem) {
            $value = $this->_searchInMacroScripts($parserItem, $pageContext);
            if (!is_null($value)) {
                $content = $this->_replace($content, $parserItem, $value);
                continue;
            }

            $value = $this->_searchInParams($parserItem, $params);
            if (!is_null($value)) {
                $content = $this->_replace($content, $parserItem, $value);
                continue;
            }
        }
        return $content;
    }

    private function _parseForMacros($content)
    {
        $patterns = [
            '/{(_?)([a-z0-9_\-]+):?([^{^}]*?)}/i',
            '/\$(_?)([a-z0-9_-]+)/i'
        ];

        $items = [];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[0] as $n => $originalSubString) {
                    $rawMode = !!$matches[1][$n];
                    $name = $matches[2][$n];
                    $args = isset($matches[3]) ? $matches[3][$n] : '';
                    $args = explode(',', $args);
                    $items[] = new ParserItem($name, $originalSubString, $rawMode, $args);
                }
            }
        }
        return $items;
    }

    /**
     * Добавляет в хэш $params еще параметры из настроек кампании
     * @param $params array
     * @param SandboxContext $pageContext
     * @return array
     */
    private function _addParamsFromCampaign($params, SandboxContext $pageContext)
    {
        $campaign = $pageContext->campaign();
        if (empty($campaign)) {
            return $params;
        }

        $campaignParameters = $campaign->getParameters();
        if (empty($campaignParameters)) {
            return $params;
        }

        foreach ($campaignParameters as $param => $paramInfo) {
            $name = null;
            if (!empty($paramInfo['name'])) {
                $name = $paramInfo['name'];
            }
            if (!empty($paramInfo['param'])) {
                $name = $paramInfo['param'];
            }

            if (!empty($name)) {
                 $params[$name] = $pageContext->rawClick()->get($param);
            }
        }

        return $params;
    }

    /**
     * Выполняет макрос-скрипт если такой находится
     * @param ParserItem $parserItem
     * @param SandboxContext $pageContext
     * @return mixed|null
     * @throws \Exception
     */
    private function _searchInMacroScripts(ParserItem $parserItem, SandboxContext $pageContext)
    {
        $macro = MacroRepository::instance()->getMacro($parserItem->name());

        if (empty($macro)) {
            return null;
        }

        if (BackCompatibility::isLegacyMacro($macro)) {
            return BackCompatibility::executeLegacyMacro($macro, $pageContext);
        }

        if (!($pageContext->stream())) {
            $e = new \Exception('pageContext must contain Stream');
            LoggerService::instance()->error($e->getMessage() . ": " . $e->getTraceAsString());
            throw $e;
        }

        switch (true) {
            case ($macro instanceof AbstractConversionMacro):
                if (!$pageContext->conversion()) {
                    return '{' . $parserItem->name() . '}';
                }
                $macrosArgs = [$pageContext->stream(), $pageContext->conversion()];
                break;

            case ($macro instanceof AbstractClickMacro):
                if (!($pageContext->rawClick())) {
                    throw new \Exception('pageContext must contain rawClick');
                }
                $macrosArgs = [$pageContext->stream(), $pageContext->rawClick()];
                break;
            default:
                throw new \Exception('Incorrect Macro type ' . get_class($macro));
        }

        $macro->setServerRequest($pageContext->serverRequest());
        if ($macro->alwaysRaw()) {
            $parserItem->setRawMode(true);
        }

        $macrosArgs = array_merge($macrosArgs, $parserItem->arguments());
        $value = call_user_func_array([$macro, 'process'], $macrosArgs);
        if (is_null($value)) {
            $parserItem->setRawMode(true);
            return $parserItem->originalString();
        }
        return $value;
    }

    /**
     * Поиск макроса среди параметров
     * @param ParserItem $parserItem
     * @param $params array
     * @return string|null
     */
    private function _searchInParams(ParserItem $parserItem, $params)
    {
        if (empty($params)) {
            return null;
        }
        if (!array_key_exists($parserItem->name(), $params)) {
            return null;
        }

        $value = $params[$parserItem->name()];
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }

    /**
     * Заменяет исходный кусок с макросом на его значение
     * @param $content
     * @param ParserItem $parserItem
     * @param $value
     * @return mixed
     */
    private function _replace($content, ParserItem $parserItem, $value)
    {
        if (!$parserItem->rawMode()) {
            $value = urlencode($value);
        }
        $content = str_replace($parserItem->originalString(), $value, $content);
        return $content;
    }
}
