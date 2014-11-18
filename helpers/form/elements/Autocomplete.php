<?php
namespace P\lib\framework\helpers\form\elements;

use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class Autocomplete extends Select
{
    protected $_jsOptions = array();
    public    $isAjax     = true;


    public function __construct($poField)
    {
        parent::__construct($poField);
    }


    public function addOption($psValue, $psLabel)
    {
        if (!$this->isAjax)
        {
            return parent::addOption($psValue, $psLabel);
        }
    }


    public function getTemplate()
    {
        if ($this->isAjax)
        {
            return 'select2.tpl.php';
        }
        else
        {
            return parent::getTemplate();
        }
    }


    protected function _configureSelect()
    {
        $this->_firstLineLabel = '';
        $this->addThemeVar('multiple', false);
        $this->addThemeVar('value', $this->getValue());
        $this->addThemeVar('id', $this->getId());

        $controller          = system\CP::get($this->_field->getForeignTable());
        $this->theme->isAjax = true;
        $this->isAjax        = true;

        if (!$controller instanceof system\interfaces\Autocomplete)
        {
            $this->theme->isAjax = false;
            $this->isAjax        = false;
//            helpers\Message::instantMessage($this->_field->getForeignTable() . ' n\'implémente pas l\'interface system-interfaces-Autocomplete', MESSAGE_ERROR);

            return '';
        }

        $this->theme->element              = $this->_themeValues;
        $this->theme->element->ajaxSource  = $controller->getAjaxSource();
        $this->theme->element->placeholder = $this->_getPlaceHolder();
        $sJS                               = $this->display('select2_JS.tpl.php');

        helpers\JSManager::addInstructions($sJS);
        $this->addJS($sJS);
    }
}