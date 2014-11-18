<?php

namespace P\lib\framework\helpers\form\elements;

class Fieldset extends Form
{
    
    public $_fields = array();
    protected $_name;
    
    public function __construct($psName)
    {
        $this->_name = $psName;
    }
    

    public function getField()
    {
        
    }


    public function __toString()
    {
        // Debug::dump($this->_fields);

        $sFields = '';
        foreach ($this->_fields as $oField)
        {
            //Debug::dump($oFormField);
            $sFields .= $oField->__toString();
        }

        $sOutput = '';
        $sOutput .= '<fieldset>'."\n";
        $sOutput .= '<legend>'.$this->_name.'</legend>'."\n";
        $sOutput .= $sFields;
        $sOutput .= '</fieldset>'."\n";

        //Debug::html($sOutput);

        return $sOutput;
    }
	
}