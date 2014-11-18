<?php
namespace P\lib\framework\core\system;
use P\apps\employee\Employee;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\utils as utils;
use P\lib\framework\core\system\interfaces as interfaces;
use Whoops\Exception\ErrorException;

/**
 *
 * Object publisher a pour rôle le rendu global de la page en cours.
 * Il initialise les composants et appelle les scripts nécessaires
 * au bon déroulement du programme demandé par l'URL
 *
 * @author Johan
 * @version 0.1
 */
class ObjectPublisher
{
    static $_controller;
    static $_action;
    static $_path;
    static $_key;
    static $_controllerName;
    public $asOutput = array();
    static $_output;


    public function __construct()
    {
        $this->_dispatch();
    }


    /**
    * Check all the parameters to handle the call of the various components called by the url
    */
    protected function _dispatch()
    {
        self::$_controllerName  = utils\Http::getInstance()->getParam(\CONTROLLER);
        self::$_action          = utils\Http::getInstance()->getParam(\ACTION);
        self::$_path            = utils\Http::getInstance()->getParam(\PATH, '');

        if (empty(self::$_controllerName))  self::$_controllerName = 'Home';
        if (empty(self::$_action))          self::$_action = 'index';

        if (!self::$_controller)
        {
            $sControllerFullName        = PathFinder::shortNameToNamespace(self::$_controllerName, self::$_path);
            self::$_controller          = ClassManager::getInstance($sControllerFullName, true);
        }

        $oTheme = \P\lib\framework\themes\ThemeManager::load();
        $oTheme->controller     = self::$_controller;
        $oTheme->master         = self::$_controller;
        $oTheme->action         = self::$_action;
        

        // on vérifie si le composant est "callable"
        if (!self::$_controller instanceof interfaces\isCallable)
        {
            helpers\Message::setMessage($sControllerFullName.' n\'est pas callable', MESSAGE_ERROR);
            self::$_output = utils\Http::error404();

            utils\Debug::e(self::$_controller);

            throw new \ErrorException($sControllerFullName.' pas callable');
            die("$sControllerFullName pas callable");
        }
        
        $sAction = self::$_action; // syntax error with direct call of self::$_action

        if (empty(self::$_output))
        {
            self::$_output = self::$_controller->$sAction();
        }
        
        
        // We check if a theme is used
        if (\P\lib\framework\themes\ThemeManager::haveTheme(self::$_controller->getName()))
        {
            // the component's output IS FORCED to be the content hook
            \P\lib\framework\themes\ThemeManager::setHookContent('content', self::$_output);

            // loading the layout
            return \P\lib\framework\themes\ThemeManager::displayLayout();
        }
        else
        {
            // we display raw data from the controller
            return trim(self::$_output);
        }

        // End of program - will be on air tomorrow at 7:00 am
        die();
    }
}
