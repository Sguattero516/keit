<?php
namespace Traffic\BackCompatibility;

use Core\Sandbox\SandboxContext;
use Traffic\Actions\Service\StreamActionService;
use Traffic\Model\BaseStream;
use Traffic\Macros\MacroRepository;
use Traffic\Pipeline\Payload;

class BackCompatibility
{
    protected static $_enabled;

    public static function init()
    {
        static::$_enabled = true;
        $loader = include(ROOT . '/vendor/autoload.php');
        $loader->addClassMap([
            'Component\Streams\Model\BaseStream' => ROOT . '/application/Traffic/BackCompatibility/classes/BaseStream.php',
            'Component\Clicks\Model\RawClick' => ROOT . '/application/Traffic/BackCompatibility/classes/RawClick.php',
            'Component\StreamActions\AbstractAction' => ROOT . '/application/Traffic/BackCompatibility/classes/AbstractAction.php',
            'Component\Macros\AbstractMacro' => ROOT . '/application/Traffic/BackCompatibility/classes/AbstractMacro.php',
            'Component\Macros\AbstractClickMacro' => ROOT . '/application/Traffic/BackCompatibility/classes/AbstractClickMacro.php',
            'Component\Macros\AbstractConversionMacro' => ROOT . '/application/Traffic/BackCompatibility/classes/AbstractConversionMacro.php',
        ]);
    }

    public static function getMacroType($obj)
    {
        $type = null;
        if (static::$_enabled) {
            switch (true) {
                case ($obj instanceof \Component\Macros\AbstractClickMacro):
                    $type = MacroRepository::CLICK;
                    break;
                case ($obj instanceof \Component\Macros\AbstractConversionMacro):
                    $type = MacroRepository::CONVERSION;
                    break;
            }
        }
        return $type;
    }

    public static function isLegacyMacro($macro)
    {
        if (!self::$_enabled) {
            return false;
        }

        return (($macro instanceof \Component\Macros\AbstractClickMacro) || ($macro instanceof \Component\Macros\AbstractConversionMacro));
    }

    public static function executeLegacyMacro($macro, SandboxContext $pageContext)
    {
        switch (true) {
            case ($macro instanceof \Component\Macros\AbstractClickMacro):
                $rawClick = new \Component\Clicks\Model\RawClick($pageContext->rawClick()->getData());
                $args = [$pageContext->stream(), $rawClick];
                break;
            case ($macro instanceof \Component\Macros\AbstractConversionMacro):
                $args = [$pageContext->stream(), $pageContext->conversion()];
                break;
            default:
                throw new \Exception(
                    'Macro ' . get_class($macro) . ' is not compatible with current version of Keitaro'
                );
        }
        $params = array_merge($args, $pageContext->serverRequest()->getAllParams());
        return call_user_func_array([$macro, 'process'], $params);
    }

    public static function isLegacyAction($action)
    {
        if (!self::$_enabled) {
            return false;
        }
        return ($action instanceof \Component\StreamActions\AbstractAction);
    }

    public static function executeLegacyAction(\Component\StreamActions\AbstractAction $action, Payload $payload)
    {
        $stream = $payload->getStream();
        $rawClick = $payload->getRawClick();

        if (empty($stream)) {
            $stream = new BaseStream();
        }
        $stream->setActionType($payload->getActionType());
        $stream->setActionPayload($payload->getActionPayload());
        $stream->setActionOptions($payload->getActionOptions());

        $destination = StreamActionService::instance()->buildDestination(
            $payload->getServerRequest(),
            $payload->getCampaign(),
            $stream->getActionPayload(),
            $stream, $rawClick
        );

        $rawClick->setDestination($destination);
        $rawClick = new \Component\Clicks\Model\RawClick($rawClick->getData());
        $stream = new \Component\Streams\Model\BaseStream();
        foreach ($rawClick->getData() as $field => $value) {
            if ($stream->hasField($field)) {
                $stream->set($field, $value);
            }
        }

        return $action->run($stream, $rawClick);
    }
}