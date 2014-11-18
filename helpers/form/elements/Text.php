<?php

namespace P\lib\framework\helpers\form\elements;

class Text extends Element
{
    protected $_type = 'text';


    public function __construct($poField, $pbIgnoreClass = false)
    {
        parent::__construct($poField);
    }


    public function getField()
    {
        if ($this->_errorStatus)
        {
            $this->addClass('error');
        }

        $this->_params['type'] = $this->_type;

        return \P\tag('input', '', $this->_params, true);
    }
}