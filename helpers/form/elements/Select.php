<?php

namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\utils\Debug as utils;
use P\lib\framework\core\utils\Debug;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class Select extends Element
{
    protected 	$_options 		= array();
    public 	$firstLine   		= true;
    protected 	$_firstLineLabel	= 'Selectionnez une valeur';
    
    
    public function addOption($psValue, $psLabel)
    {
        $this->_options[$psValue] = $psLabel;
    }

    
    public function populateFromArray($pasArray)
    {
        if (!is_array($pasArray)) throw new \ErrorException('param submitted must be an array');
        foreach($pasArray as $nValue => $sLabel)
        {
            $this->addOption($nValue, $sLabel);
        }
    }
    
    
    /**
     * We check if the select can populate itself.
     * if possible, we populate from transversal methods
     */
    public function populate()
    {
        $sForeignTable  = $this->_schemaField->getForeignTable();
        $sForeignField  = $this->_schemaField->getForeignField();
        $sForeignLabel  = $this->_schemaField->getForeignLabelField();
        
        if (!empty($sForeignTable) && !empty($sForeignField) && !empty($sForeignLabel))
        {
            $oClass = system\ClassManager::getInstance($sForeignTable);
            
            if ($oClass instanceof system\abstractClasses\Controller)
            {
                $oClass->populateForeignField($this->_schemaField, $this);
            }
        }
        
//        Debug::e($this->_options);
    }

    
    protected function _configureSelect()
    {
        $this->addThemeVar('multiple', false);
        $this->addThemeVar('values', $this->getValue());
    }
    
    /**
     * @see Element::getField()
     */
    function getField()
    {
        $this->addThemeVar('id', $this->getId());
        $this->_configureSelect();
        
    	if ($this->_errorStatus)
            $this->addClass('error');
			
        $this->addClass('required');

        if ($this->firstLine && !empty($this->_firstLineLabel))
        {
            if (empty($this->_options))
                $this->addOption(0, $this->getFirstLineLabel());
        }
        
        $this->populate();
    	
        $this->addThemeVar('options', $this->_options);
        $this->addThemeVar('value', $this->getValue());
        
        
//        Debug::e($this->_options);
        
        $this->theme->element = $this->_themeValues;
        $sRender = $this->display('select.tpl.php');
        
//        Debug::e(htmlentities($sRender));
        
        return $sRender;
    }
    
    
    public function setFirstLineLabel($psLabel)
    {
    	$this->_firstLineLabel = $psLabel;
    }
    
    
    public function getFirstLineLabel()
    {
    	return $this->_firstLineLabel;
    }
}