<?php

namespace P\lib\framework\helpers\facebook;

use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;


/**
 * Description of Video
 *
 * @author Johan
 */
class Video 
{
    public function __construct($poData, $poFeed)
    {
        
        // checking if the author is the owner of the page
        if ($poData->from->id != $poFeed->oFeed->id) return false;
        
        $oPublication = \P\lib\framework\core\system\ClassManager::getInstance('publication');

        // Checking if this entry is already saved
        if($oPublication->model->countByFacebookId($poData->id)) return 'deja enregistré';
        
        $this->token    = $poFeed->sToken;
        
        $sDateCreate = date('Y-m-d H:i:s', strtotime($poData->created_time));
        $sDateUpdate = date('Y-m-d H:i:s', strtotime($poData->updated_time));

        utils\Debug::log('avant save');
        $nPublicationPK = $oPublication->model->save(array(
            'title'                     => $poData->name,
            'message'                   => $poData->description,
            'source'                    => str_replace('autoplay=1', 'autoplay=0', $poData->source),
            'link'                      => $poData->link,
            'facebook_id'               => $poData->id,
            'type'                      => 'video',
            'publication_date_create'   => $sDateCreate,
            'publication_date_update'   => $sDateUpdate,
            'entityfk'                  => $poFeed->nEntityFK,
            'facebook_accountfk'        => $poData->from->id,
            'raw_source'                => serialize($poData)
        ));
        
        return (bool) $nPublicationPK;
    }
    
    
    public function __toString()
    {
        return '';
    }
}

?>
