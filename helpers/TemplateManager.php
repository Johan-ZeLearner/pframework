<?php
namespace P\lib\framework\helpers;

class TemplateManager
{
    /*
     * @var P\lib\extra\Savant3\Savant3
     */
    static $template;
    
    public static function load()
    {
        self::$template = new P\lib\extra\Savant3\Savant3();
    }
    
    
    public static function assign($psName, $psValue)
    {
        self::$template->$psName = $psValue;
    }
    
     
    public static function display($psTemplateName)
    {
        self::$template->display($psTemplateName);
    }
}