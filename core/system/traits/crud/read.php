<?php
namespace P\lib\framework\core\system\traits\crud;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;

trait read 
{
    public function read()
    {
        try
        {
            // return a DbResponse object
            $oResults = $this->getReadSelect();
        }
        catch (Exception $e)
        {
            //todo wrap into debug messages class
            echo 'This app doesn\'t have a Model - This trait ::read must access the Model';
            return false;
        }
        
        // generating the temlplate output
        $oTheme = \P\lib\framework\themes\ThemeManager::load();
        
        $oTheme->tableHeader    = $this->model->getFieldLabels('browsable');
        $oTheme->fields         = $this->model->getFieldNames('browsable');
        $oTheme->data           = $oResults;
        $oTheme->count          = $oResults->count;
        $oTheme->title          = $this->_getTitle('index');
        $oTheme->actions        = $this->_getActions();
        $oTheme->controller     = $this;
        $oTheme->primary        = $this->model->getPrimary();
        
        return $oTheme->display($this->getReadTemplate());
    }
    
    
    public function getReadSelect()
    {
        return $this->model->select();
    }
    
    
    public function getReadTemplate()
    {
        return 'trait_read_read.tpl.php';
    }
    
    
    public function getActionLink($pnPK, $psAction)
    {
        switch ($psAction)
        {
            case 'update':
            case 'edit':
                return \P\url('', 'update', array('key' => $pnPK));
                break;
            
            case 'delete':
                return \P\url('', 'delete', array('key' => $pnPK));
                break;
        }
        
        return '';
    }
    
    
    protected function _handleheader($poRecord)
    {
        echo '<pre>';
        print_r($poRecord);
        die();
    }
    
    
    protected function _getReadActionButton()
    {
        $asActions = array();
        
        // add
        $asActions[0]['link']   = \P\url('', 'create');
        $asActions[0]['label']   = 'Ajouter';
        $asActions[0]['css']    = 'btn-success';
        $asActions[0]['icon']   = 'icon-plus-sign icon-white';

        return $asActions;
        
        //return array_merge($asActions, $this->getActionButton());
    }
    
    
    protected function _getActions()
    {
        return $this->_getReadActionButton();
        
        $oUrl = new utils\Url(); // $oUrl->setParam('action', 'create');
        $oUrl->setParam('action', 'create');
        
        return array(
            'Ajouter' => $oUrl
        );
    }
    
    
    public function index()
    {
        return $this->read();
    }
    
}