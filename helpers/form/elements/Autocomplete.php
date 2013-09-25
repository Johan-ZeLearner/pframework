<?php
namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class Autocomplete extends Select
{
    protected $_jsOptions = array();
    
    public function __construct($poField)
    {
        parent::__construct($poField);
    }
    
    
    protected function _configureSelect()
    {
        $this->_firstLineLabel = '';
        $this->addThemeVar('multiple', false);
        $this->addThemeVar('value', $this->getValue());
        $this->addThemeVar('id', $this->getId());
        
        $this->theme->element = $this->_themeValues;
        $sJS = $this->display('select2_JS.tpl.php');

        helpers\JSManager::addInstructions($sJS);
        $this->addJS($sJS);
    }
}