<?php

namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;

class Date extends Text
{
    public function getField()
    {
        if (!isset($this->_params['id'])) $this->_params['id'] = uniqid('date');

        helpers\JSManager::addFile('js/datepicker/js/bootstrap-datepicker.js');
        helpers\CssManager::addFile('js/datepicker/css/datepicker.css');
        
        
        $sJS = '
            $(function() {
		 jQuery( "#'.$this->_params['id'].'" ).datepicker(
                     {
                        days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"],
                        daysShort: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"],
                        daysMin: ["D", "L", "Ma", "Me", "J", "V", "S", "D"],
                        months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
                        monthsShort: ["Jan", "Fev", "Mar", "Avr", "Mai", "Jui", "Jul", "Aou", "Sep", "Oct", "Nov", "Dec"],
                        today: "Aujourd\'hui",
                        weekStart: 1,
                        format: "dd/mm/yyyy"
                    });
            });
        ';

        $this->addJS($sJS);
        helpers\JSManager::addInstructions($sJS);

        if (utils\Date::isEmpty($this->_field->value))
        {
            $this->_field->value 	= '';
            $this->_params['value']     = '';
        }
        else
        {
            $this->_field->value 	= utils\Date::toDisplay($this->_field->value, true);
            $this->_params['value']     = utils\Date::toDisplay($this->_field->value, true);
        }
        
        return parent::getField();
    }
}