<?php

namespace P\lib\framework\helpers\facebook;

use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;

class Likes extends GraphElement
{
    protected $element = 'likes';
    
    protected function _process($psJSON)
    {
        $oData = json_decode($psJSON);
        
        if (isset($oData->likes))
            return $oData->likes->data;
        
        return array();
    }
}