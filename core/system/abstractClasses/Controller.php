<?php
namespace P\lib\framework\core\system\abstractClasses;

use P\apps\employee\Employee;
use P\lib\framework\core\system as system;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system\interfaces as interfaces;
use P\lib\framework\core\system\traits as traits;
use P\lib\framework\themes\ThemeManager;
use P\override\abstractClasses\SubAction;

abstract class Controller extends Object implements interfaces\isCallable
{
    /**
     * @var Model
     */
    public $model;

    /**
     * @var type ThemeManager
     */
    public    $theme;
    public    $context;
    protected $_oRights;
    static    $oaClasses = array();


    public function __get($name)
    {

        switch ($name)
        {
            case 'controllerName':
                return 'Nom du controller à définir';
        }
    }


    public function __construct($poModel = '')
    {
        $this->theme = \P\lib\framework\themes\ThemeManager::load();

        if (is_object($poModel))
        {
            $this->model = $poModel;
        }

        return parent::__construct();
    }


    public function getControllerName()
    {
        return $this->controllerName;
    }



//    public function returnBreadCrumb($action, $key=0)
//    {
//        return $this->breadcrumb;
//    }


    public function getIcon()
    {
        return '';
    }


    public function info()
    {
//        throw new \ErrorException('ghjkl');
        phpinfo();
        die();
    }


    /**
     * Returns the name of the class (even if it is called by a child class)
     */
    public function getName()
    {
        return get_called_class();
    }


    public function getViewDir()
    {
        return system\PathFinder::getViewDir($this->getName());
    }


    public function __call($name, $arguments)
    {

        $className        = get_class($this);
        $currentNamespace = system\PathFinder::getControllerNamespace($className);

        $className = $currentNamespace . '\\classes\actions\\' . $name . '\\' . ucfirst($name);

//
//        if (Employee::isJohan())
//        {
//            $controller = $className::getInstance();
//
//            if ($controller instanceof SubAction)
//            {
//                return $controller->dispatch();
//            }
//        }


        try
        {
            $controller = $className::getInstance();

            if ($controller instanceof SubAction)
            {
                return $controller->dispatch();
            }

            return $controller->$name();
        }
        catch (\ErrorException $e)
        {
            return utils\Http::error501(true, $className . ' unknown');
//            die('methode inconnue');
        }

    }


    public function getTitle($psName = '', $psAction = '')
    {
        return $this->_getTitle($psAction);
    }


    public function getActionButton()
    {
        return array();
    }


    public function getTableName()
    {
        if ($this->model instanceof Model)
        {
            return $this->model->getTable();
        }
    }


    public function populateForeignField($poDalField, $poFormField)
    {
        system\ForeignFieldData::populate($this, $poDalField, $poFormField);
    }


    public function getLabel($pnPK)
    {
        if ($pnPK == 0)
        {
            return 'aucun';
        }

        $sFieldLabel = $this->model->getLabelFieldName();

        $oRecord = $this->model->selectByPK($pnPK);

        //utils\Debug::dump($oRecord);

        if (isset($oRecord->$sFieldLabel))
        {
            return $oRecord->$sFieldLabel;
        }
    }


    protected function getKey()
    {
        return (int)utils\Http::getParam('key');
    }


    protected function _getTitle($psAction = '')
    {
        if (empty($psAction))
        {
            $psAction = \P\get(ACTION);
        }

        if (!is_object($this->model))
        {
            return '';
        }


        $sVar = strtoupper($this->model->getTable());

        if (!defined('INDEX_' . $sVar))
        {
            return 'Titre de la page';
        }

        switch ($psAction)
        {
            case 'read':
            case 'index':
                return constant('INDEX_' . $sVar);

            case 'create':
                return constant('ADD_' . $sVar);

            case 'delete':
                return constant('DELETE_' . $sVar);

            case 'update':
                return constant('EDIT_' . $sVar);
        }

        return '';
    }


    protected function _disableControllerCall()
    {

    }


    public function ajax()
    {
        \P\lib\framework\themes\ThemeManager::setAjax();

        $sAjax = strtolower(utils\Http::getParam('ajax'));

        switch ($sAjax)
        {
            case 'editinplace':
                return $this->_ajaxEditInPlace();
                break;

//           case 'upload':
//               return $this->_ajaxUpload();
//               break;

            default:
                return $this->_ajaxCustomCall($sAjax);
        }
    }


    protected function _ajaxCustomCall($psAjax)
    {
        return json_encode(array('type'    => 'error',
                                 'message' => 'AbstractController : no ajax output'
            ));
    }


    protected function _ajaxEditInPlace()
    {
        $nKey   = utils\Http::getParam('pk');
        $sName  = utils\Http::getParam('name');
        $sValue = utils\Http::getParam('value');

        if ($nKey > 0 && in_array($sName, $this->model->getFieldNames()))
        {
            $this->model->save(array($sName => $sValue), $nKey);
        }
    }


    protected function _ajaxUpload()
    {
        return '';
    }


    public static function registerClass($poObject)
    {
        if (!isset(self::$oaClasses[$poObject->getName()]))
        {
            self::$oaClasses[$poObject->getName()] = $poObject;
        }
        else
        {
            unset ($poObject);
        }

    }


    public function loadFormData($psType, $oForm)
    {
        return true;
    }
}