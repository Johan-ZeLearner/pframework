<?php

namespace P\lib\framework\helpers\form\elements;

class Checkbox extends Radio
{
	public function getField()
	{
            $bChecked = false;
            if ($this->_field->value == 1)
            {
                $bChecked = true;
            }
            
            $this->addThemeVar('checked', $bChecked);
            $this->addThemeVar('value', 1);
            $this->addThemeVar('label', $this->_label);
            $this->simple = true;

            $this->theme->element = $this->_themeValues;
            return $this->display('checkbox.tpl.php');
	}
}