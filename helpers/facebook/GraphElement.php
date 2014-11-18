<?php

namespace P\lib\framework\helpers\facebook;

use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;

abstract class GraphElement
{
    protected $element = '';
    
    public function getFeed($psFacebookId, $psToken)
    {
        $sUrl = 'https://graph.facebook.com/'.$psFacebookId.'?fields='.$this->element;
        $sUrl .= '&access_token='.$psToken;
        
        $sJSONResults = file_get_contents($sUrl);
        
        return $this->_process($sJSONResults);
    }
    
    abstract protected function _process($psJSON);
}