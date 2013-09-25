<?php
namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\utils\Debug as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class Time extends Text
{
	public function getField()
	{
            if (!isset($this->_params['id'])) $this->_params['id'] = uniqid('date');

            helpers\JSManager::addFile('timePicker/jquery.ui.timepicker.js');
            helpers\CSSManager::addFile('../js/timePicker/jquery-ui-timepicker.css');

            $sJS = '
                $(function() {
                    $( "#'.$this->_params['id'].'" ).timepicker({
                        hourText: "Heures",
                        minuteText: "Minutes"
                    });
                });
            ';

            $this->addJS($sJS);
            helpers\JSManager::addInstructions($sJS);

            if ($this->_field->value == '01/01/1970' || empty($this->_field->value))
            {
                $this->_field->value 	= '';
                $this->_params['value'] = '';
            }
            else
            {
                $this->_field->value 	= $this->_field->value ;
                $this->_params['value'] = $this->_field->value ;
            }

            return parent::getField();
	}
}