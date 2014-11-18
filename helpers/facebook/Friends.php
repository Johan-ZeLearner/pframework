<?php

namespace P\lib\framework\helpers\facebook;

use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;

class Friends extends GraphElement
{
    protected $element = 'friends';
    
    protected function _process($psJSON)
    {
        $oData = json_decode($psJSON);
        
        return $oData->friends->data;
    }
}