<?php

namespace P\lib\framework\helpers\facebook;

use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;

class PhotoSimple
{
    private $token;
    
    public function __construct($poData, $poFeed)
    {
        utils\Debug::log('['.date('H:i:s').']----------------- Debug PHOTO '.$poData->id.' ---------------');
        
        // checking if the author is the owner of the page
        if ($poData->from->id != $poFeed->oFeed->id)
        {
            utils\Debug::log('Author is not the owner');
            return false;
        }
        
        $oPublication = \P\lib\framework\core\system\ClassManager::getInstance('publication');

        // Checking if this entry is already saved
        if($oPublication->model->countByFacebookId($poData->id)) return true;
        
        $this->token    = $poFeed->sToken;
        $nImageFK       = (int) $this->_createImage($poData->object_id, $poData->picture);
        
        if($nImageFK == 0) return false;
        
        $sDateCreate = date('Y-m-d H:i:s', strtotime($poData->created_time));
        $sDateUpdate = date('Y-m-d H:i:s', strtotime($poData->updated_time));

        $sMessage = '';
        if (isset($poData->message))
        {
            $sMessage = $poData->message;
        }
        elseif (isset($poData->comments->data[0]->from->id) && $poData->comments->data[0]->from->id == $poFeed->nFacebookId)
        {
            $sMessage = $poData->comments->data[0]->message;
        }
        elseif (isset($poData->caption))
        {
            $sMessage = $poData->caption;
        }

        $sTitle = '';
        if (isset($poData->name))
            $sTitle = $poData->name;
        
        $nPublicationPK = $oPublication->model->save(array(
            'title'                     => $sTitle,
            'message'                   => $sMessage,
            'link'                      => $poData->link,
            'facebook_id'               => $poData->id,
            'type'                      => 'photo',
            'publication_date_create'   => $sDateCreate,
            'publication_date_update'   => $sDateUpdate,
            'imagefk'                   => $nImageFK,
            'entityfk'                  => $poFeed->nEntityFK,
            'facebook_accountfk'        => $poData->from->id,
            'raw_source'                => serialize($poData)
        ));
        
        return (bool) $nPublicationPK;
    }
    
    
    protected function _createImage($nObjectId, $sPicture)
    {
        $sDescription = '';
        $sName = '';
        $sFaceBookId    = $nObjectId;
        $sSource        = str_replace('_s.', '_o.', $sPicture);
        
        $oImage = \P\lib\framework\core\system\ClassManager::getInstance('image');
        
        return $oImage->saveFromfacebook($sSource, $sFaceBookId, $sName, $sDescription);
    }
    
    
    private function _getName($psName)
    {
        $asName = explode("\n", $psName);
        
        return $asName[0];
    }
    
    
    public function __toString()
    {
        return '';
    }
}
?>
