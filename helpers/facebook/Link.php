<?php

namespace P\lib\framework\helpers\facebook;

use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;

class Link
{
    public function __construct($poData, $poFeed)
    {
        if (isset($poData->status_type) && $poData->status_type == 'approved_friend')
            return false;
        
        if (isset($poData->story) && preg_match('/went to an event/', $poData->story))
            return false;
        
        if (isset($poData->story) && preg_match('/created an event/', $poData->story))
            return false;
        
        if (isset($poData->story) && preg_match('/likes/', $poData->story))
            return false;
        

        utils\Debug::log('['.date('H:i:s').']----------------- Debug LINK '.$poData->id.' ---------------');
        
        // checking if the author is the owner of the page
        if ($poData->from->id != $poFeed->oFeed->id) return false;
        
        $oPublication = \P\lib\framework\core\system\ClassManager::getInstance('publication');

        // Checking if this entry is already saved
        if($oPublication->model->countByFacebookId($poData->id)) return true;
        
        utils\Debug::log('pas de doublon');
        
        $this->token    = $poFeed->sToken;
        
        $sDateCreate = date('Y-m-d H:i:s', strtotime($poData->created_time));
        $sDateUpdate = date('Y-m-d H:i:s', strtotime($poData->updated_time));

        
        $sDescription = '';
        if (isset($poData->description))
            $sDescription = $poData->description;
        elseif (isset($poData->message))
            $sDescription = $poData->message;
        
        $sName = '';
        if (isset($poData->name))
            $sName = $poData->name;
        elseif(isset($poData->caption))
            $sName = $poData->caption;
        
        $nImageFK = 0;
        if (isset($poData->picture))
            $nImageFK       = (int) $this->_createImage($poData->picture, $poData->id, 'Lien : '.$sName, $sDescription);
        
        $nPublicationPK = $oPublication->model->save(array(
            'title'                     => $sName,
            'message'                   => $sDescription,
            'link'                      => $poData->link,
            'facebook_id'               => $poData->id,
            'type'                      => 'link',
            'publication_date_create'   => $sDateCreate,
            'publication_date_update'   => $sDateUpdate,
            'imagefk'                   => $nImageFK,
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