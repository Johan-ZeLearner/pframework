<?php

namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\helpers as helpers;

class Radio extends Select
{
    protected $_type = 'radio';
	
    function getField ()
    {
        $this->addClass('radio');
        $this->_params['type'] = $this->_type;
        $this->_populate();
        $sOutput = '';
        
        $sFieldValue = 'null';
        if (isset($this->_params['value']))
        {
            $sFieldValue = $this->_params['value'];
            unset($this->_params['value']);
        }
        
        $i = 0;
        foreach ($this->_options as $sLabel => $sValue)
        {
            $asParams = $this->_params;
            
            if ($sValue == $sFieldValue)
                $asParams['checked'] = 'checked';
                
            if (isset($asParams['id']))
                $asParams['id'] = $asParams['id'].'_'.$i;
                
             $asParams['value'] = $sValue;

            $sOutput .= \P\tag('input', '', $asParams, true).\P\tag('label', $sLabel, array('for' => $asParams['id'], 'class' => 'radio'));
            
            $i++;
        }
        
        
        $sOutput .= \P\tag('div', $sOutput, array('class' => 'buttonset'));
        
        $sJS = '
            $(function() {
                $(".buttonset").buttonset();
                $(".buttonset").css("border-color", "red");
            });
        
        ';
        
       $this->addJS($sJS);
       helpers\JSManager::addInstructions($sJS);
        
        return $sOutput;
    }
}