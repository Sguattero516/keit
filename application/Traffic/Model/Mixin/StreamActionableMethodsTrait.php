<?php
namespace Traffic\Model\Mixin;

use Traffic\Model\StreamActionCategory;

trait StreamActionableMethodsTrait
{
    public function getId()
    {
        return $this->get('id');
    }

    public function getActionPayload()
    {
        return $this->get('action_payload');
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function getType()
    {
        return $this->hasField('landing_type') ? $this->get('landing_type') : $this->get('offer_type');
    }

    public function isLocal()
    {
        return $this->getType() === StreamActionCategory::LOCAL;
    }

    public function isPreloaded()
    {
        return $this->getType() === StreamActionCategory::PRELOADED;
    }

    public function isOtherAction()
    {
        return $this->getType() === StreamActionCategory::OTHER;
    }

    public function getOtherAction()
    {
        return $this->get('action_type');
    }

    public function setActionType($value)
    {
        return $this->set('action_type', $value);
    }

    public function getActionType()
    {
        return $this->get('action_type');
    }

    public function setActionOptions($value)
    {
        $this->set('action_options', $value);
    }

    public function setActionOption($key, $value)
    {
        if (!$this->getActionOptions()) {
            $this->setActionOptions([]);
        }
        $options = $this->getActionOptions();
        $options[$key] = $value;
        $this->setActionOptions($options);
    }

    public function getActionOptions()
    {
        return $this->get('action_options');
    }

    public function getFolder()
    {
        if ($this->getActionOptions()  && isset($this->getActionOptions()['folder'])) {
            return $this->getActionOptions()['folder'];
        } else {
            return null;
        }
    }

    public function setFolder($folder)
    {
        $actionOptions = $this->getActionOptions();
        $actionOptions['folder'] = $folder;
        $this->setActionOptions($actionOptions);
        return $this;
    }
}