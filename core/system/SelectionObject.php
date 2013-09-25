<?php

namespace P\lib\framework\core\system;

class SelectionObject
{
    public $_asData; // public for iterating

    public function __construct($pasData)
    {
        $this->_asData = $pasData;
    }


    public function getData()
    {
        return $this->_asData;
    }
}