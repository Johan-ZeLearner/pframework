<?php

namespace P\lib\framework\helpers\form\elements;

use P\lib\framework\core\utils\Debug;

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
        $this->theme->element        = $this->_themeValues;
        $this->theme->element->label = $this->_field->label;

        return $this->display('checkbox.tpl.php');
    }
}