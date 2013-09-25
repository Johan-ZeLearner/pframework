<?php

namespace P\lib\framework\helpers\facebook;

use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;


class PhotoGalery
{
    private $token;
    
    public function __construct($poData, $poFeed)
    {
        
        // checking if the author is the owner of the page
        if ($poData->from->id != $poFeed->nFacebookId) return false;
        
        $oPublication = \P\lib\framework\core\system\ClassManager::getInstance('publication');
        
        // Checking if this entry is already saved
        if($oPublication->model->countByFacebookId($poData->id)) return true;
        
        $this->token    = $poFeed->sToken;
        $nGaleryFK      = $this->_createGallery($poData->object_id);
    }
    
    
    private function _createGallery($pnObjectId)
    {
        $sUrl = 'https://graph.facebook.com/'.$pnObjectId.'?method=GET&metadata=true&format=json&access_token='.$this->token;
        
        $sQueryString = file_get_contents($sUrl);
        
        $asData = json_decode($sQueryString);
        
        utils\Debug::e($asData);
        
        die();
    }
}
?>
