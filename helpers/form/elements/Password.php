<?php

namespace P\lib\framework\helpers\form\elements;

class Password extends Text
{
    public function __construct($poField)
    {
        $this->_type = 'password';
        parent::__construct($poField);
    }

    public function getField()
    {
        $this->setValue('');
        return parent::getField();
    }
}