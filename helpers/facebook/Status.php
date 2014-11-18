<?php

namespace P\lib\framework\helpers\facebook;

use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;

class Status
{
    public function __construct($poData, $poFeed)
    {
        if (isset($poData->status_type) && $poData->status_type == 'approved_friend')
            return false;
        elseif(!isset($poData->message))
            return false;
        
        utils\Debug::e('['.date('H:i:s').']----------------- Debug LINK '.$poData->id.' ---------------');
        
        // checking if the author is the owner of the page
        if ($poData->from->id != $poFeed->oFeed->id) 
        {
            utils\Debug::e('Pas de le bon auteur');
            return false;
        }
        
        utils\Debug::log('id ok');
        
        $oPublication = \P\lib\framework\core\system\ClassManager::getInstance('publication');

        // Checking if this entry is already saved
        if($oPublication->model->countByFacebookId($poData->id)) return true;
        
        $this->token    = $poFeed->sToken;
        
        $sDateCreate = date('Y-m-d H:i:s', strtotime($poData->created_time));
        $sDateUpdate = date('Y-m-d H:i:s', strtotime($poData->updated_time));

        
        $nPublicationPK = $oPublication->model->save(array(
            'message'                   => $poData->message,
            'facebook_id'               => $poData->id,
            'type'                      => 'status',
            'publication_date_create'   => $sDateCreate,
            'publication_date_update'   => $sDateUpdate,
            'entityfk'                  => $poFeed->nEntityFK,
            'facebook_accountfk'        => $poData->from->id,
            'raw_source'                => serialize($poData)
        ));
        
        return (bool) $nPublicationPK;
    }
    
    
    protected function _createImage($sSource, $psId, $psName, $psDescription)
    {
        utils\Debug::e('creation');
        
        $oImage = \P\lib\framework\core\system\ClassManager::getInstance('image');
        
        return $oImage->saveFromfacebook($sSource, $psId, $psName, $psDescription);
    }
    

    public function __toString()
    {
        return '';
    }
}