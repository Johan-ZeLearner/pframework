<?php
namespace P\lib\framework\core\system\traits;

trait formTemplating
{
    protected   $_themeValues;
    public      $theme;
    
    
    public function initializeFormTemplating()
    {
        $this->theme = \P\lib\framework\themes\ThemeManager::load();
        $this->_themeValues = new \stdClass();
        $this->_themeValues->values = array();
    }
    
    public function addThemeVar($psName, $psValue)
    {
        $this->_themeValues->$psName = $psValue;
    }
    
    
    public function display($psFile)
    {
        return $this->theme->display(__DIR__.'/../../../helpers/form/template/'.$psFile, true);
    }
}

?>
