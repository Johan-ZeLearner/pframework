<?php
namespace P\lib\framework\core\system\traits\crud;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;

trait readajaxCustom
{
    use readajax;
    
    protected function _read()
    {
        $this->setTableOptions();
        
        $this->theme->tableHeader    = $this->readajax_getTableHeader(true, true);
        $this->theme->fields         = $this->readajax_getFieldNames();
        $this->theme->ajaxSource     = $this->readajax_getAjaxSource();
        $this->theme->title          = $this->_getTitle('index');
        $this->theme->actions        = $this->_getActions();
        $this->theme->controller     = $this;
        $this->theme->master         = $this;
        
        return $this->theme->display($this->getReadAjaxTemplate());
    }
}