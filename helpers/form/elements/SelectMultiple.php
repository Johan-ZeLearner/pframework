<?php

namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\utils\Debug as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class SelectMultiple extends Select
{
    protected $_asSelectedValues = array();
    protected $_asSelectedLabels = array();

    public function addSelectedValue($psValue)
    {
        $this->_asSelectedValues[] = $psValue;
    }
    

    public function getSelectedValues()
    {
        if (empty($this->_asSelectedValues) && isset($this->_params['value']))
            $this->addSelectedValue($this->_params['value']);

        return $this->_asSelectedValues;
    }

    
    protected function _configureSelect()
    {
        if (!in_array($this->getValue(), $this->getSelectedValues()))
            $this->addSelectedValue ($this->getValue());
        
        $this->addThemeVar('multiple', true);
        $this->addThemeVar('values', $this->getSelectedValues());
    }
}