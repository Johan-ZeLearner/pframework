<?php

namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;

class Tags extends Autocomplete
{
    protected $_asTags = array();

    public function addTag($pnKey, $psLabel)
    {
        $this->_asTags[$pnKey] = $psLabel;
    }
	
    
    public function setValue($psValue, $psLabel = '') 
    {
        if (is_array($psValue))
        {
            $sForeignTable = $this->_field->getForeignTable();
    	
            if (empty($psLabel) && !empty($psValue) && !empty($sForeignTable))
            {
                foreach ($psValue as $nKey)
                {
                    $sLabel = $this->getLabel($nKey, $sForeignTable);
                    $this->addTag($nKey, $sLabel);
                }
            }
        }
    }
    
	
    protected function _getCustomOutput()
    {
        
       	$sOutput = \P\tag('input', '', array('id' => 'input_'.$this->getId(), 'class' => 'txt'), true);
    
    	$sOutput .= \P\tag('div', $this->_createTags(), array('id' => 'tags_'.$this->getId()));

    	return $sOutput;
    }
	
    
    protected function _getJqueryConfig()
    {
    	// general
    	helpers\JSManager::addFile('js/form/tags.js');
    	helpers\JSManager::addFile('js/utils/uniqid.js');
        
    	// form    	
    	$sJS = '
            function( event, ui )
            {
                tags_addTag(ui.item.key, ui.item.label, "list_'.$this->getId().'", "'.$this->getId().'", this);
                return false;
            }
    	';
    	
    	$this->_jsOptions['click']      = utils\Tokenizer::tokenize($sJS);
    	$this->_jsOptions['select']     = utils\Tokenizer::tokenize($sJS);
    	//$this->_jsOptions['focus']    = Tokenizer::tokenize($sJS);
    	
    	
    	// events "close"
    	$sJS = '
    	
    		$(".form_field_tag").bind("click",  function(){tag_delete(this);});
    	
    	';
    	
    	helpers\JSManager::addInstructions($sJS);
    	$this->addJS($sJS);
    	
    }
    
    
    protected function _createTags()
    {
    	$sOutput = '';
    	
    	foreach ($this->_asTags as $nKey => $sLabel)
    	{
            $sTagId = uniqid('id_');

            $sHidden = \P\tag('input', '', array('type' => 'hidden', 'value' => $nKey, 'name' => $this->getId().'[]', 'id' => 'hidden_'.$sTagId, 'label' => $sLabel), true);
            $sOutput .= \P\tag('button', '<i class="icon-remove-sign icon-white"></i> '.$sLabel, array('class' => 'btn btn-primary form_field_tag', 'id' => $sTagId, 'type' => 'button')).$sHidden.' ';
    	}
    	
    	return '<div class="clearfix">&nbsp;</div>'.\P\tag('p', $sOutput, array('id' => 'list_'.$this->getId()));
    }
}
?>