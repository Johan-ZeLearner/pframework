<?php

namespace P\lib\framework\helpers\form\elements;

class Hidden extends Text
{
    public function __construct($poField)
    {
        $this->_type = 'hidden';
        parent::__construct($poField);
    }
}