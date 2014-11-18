<?php

namespace P\lib\framework\helpers\form\elements;

class Ghost extends Text
{
    protected $_type;

    public function __construct($poField, $pbIgnoreClass=false)
    {
        parent::__construct($poField);

        $this->simple = true;
    }


    public function getField()
    {
        return '';
    }
}