<?php

namespace P\lib\framework\helpers\facebook;
use P\lib\framework\core\utils as utils;
use P\lib\framework\core\helpers as helpers;

class Feed
{
    public $oFeed;
    public $sToken;
    public $nFacebookId;
    public $nEntityFK;
    
    public $feedLimit = 100;
    
    public function __construct($pnFacebookId, $psToken, $pnEntityFK)
    {
        $this->sToken       = $psToken;
        $this->nFacebookId  = $pnFacebookId;
        $this->nEntityFK    = $pnEntityFK;
        $this->depht        = 0;
    }

    
    public function loadFeed($psFacebookId, $pnDepht=0)
    {
        $this->depht = $pnDepht;
        
        $sFilename = date('Y-m-d-H-i').'_'.$this->nFacebookId.'x.txt';
        $sFilePath = \P\lib\framework\core\system\PathFinder::getTempDir().$sFilename;
        
        
        $oEntity = \P\lib\framework\core\system\ClassManager::getInstance('entity');
        $oRecord = $oEntity->model->selectByPK($this->nEntityFK);
        
        $sUrl = 'https://graph.facebook.com/'.$this->nFacebookId.'?fields=feed.limit('.$this->feedLimit.')';
        
        
        $oFacebookAccount = \P\lib\framework\core\system\ClassManager::getInstance('facebook_account');
        $oFbRecord = $oFacebookAccount->model->selectByfacebookId($psFacebookId);
        
        if ($oFbRecord->type == 'pages')
        {
            $sUrl .= '&access_token='.$this->sToken;

            $sFeed = file_get_contents($sUrl);
        }
        elseif($oFbRecord->type == 'profil' || $oFbRecord->type == 'friends')
        {
            $oLogin = \P\lib\framework\core\system\ClassManager::getInstance('login');
            $oRecord = $oLogin->model->load(2);
            
            $sToken =  $oLogin->model->token;
            
            $sUrl .= '&access_token='.$sToken;
            
            $sFeed = file_get_contents($sUrl);
        }
        else
        {
            die('Type facen=book inconnu');
        }
        
        $this->oFeed        = json_decode($sFeed);
        $this->nFacebookId  = $this->oFeed->id;
        
        file_put_contents($sFilePath, serialize($this->oFeed));
    }

    
    public function loadGaleryFeed($psFacebookId)
    {
        $sFilename = date('Y-m-d-H').'_galery_'.$this->nFacebookId.'azcd.txt';
        $sFilePath = \P\lib\framework\core\system\PathFinder::getTempDir().$sFilename;
        
        $oEntity = \P\lib\framework\core\system\ClassManager::getInstance('entity');
        $oRecord = $oEntity->model->selectByPK($this->nEntityFK);
        
        $sUrl = 'https://graph.facebook.com/'.$this->nFacebookId.'?fields=albums.fields(photos)';
            
        $oFacebookAccount = \P\lib\framework\core\system\ClassManager::getInstance('facebook_account');
        $oFbRecord = $oFacebookAccount->model->selectByfacebookId($psFacebookId);
        
        
        if ($oFbRecord->type == 'pages')
        {
            $sUrl .= '&access_token='.$this->sToken;

            //utils\Debug::e($oFbRecord->type.' - '.$sUrl);
            
            $sFeed = file_get_contents($sUrl);
        }
        elseif($oFbRecord->type == 'profil' || $oFbRecord->type == 'friends')
        {
            $oLogin = \P\lib\framework\core\system\ClassManager::getInstance('login');
            $oRecord = $oLogin->model->load(2);
            
            $sToken =  $oLogin->model->token;
            
            $sUrl .= '&access_token='.$sToken;

           // utils\Debug::e($oFbRecord->type.' - '.$sUrl);
            
            $sFeed = file_get_contents($sUrl);
        }
        else
        {
            die('Type facen=book inconnu');
        }
        
        $this->oFeed        = json_decode($sFeed);
        $this->nFacebookId  = $this->oFeed->id;
        
      //  file_put_contents($sFilePath, serialize($this->oFeed));
    }
    
    
    public function loadEventFeed($psFacebookId)
    {
        $sFilename = date('Y-m-d-H').'_galery_'.$this->nFacebookId.'azcd.txt';
        $sFilePath = \P\lib\framework\core\system\PathFinder::getTempDir().$sFilename;
        
        $oEntity = \P\lib\framework\core\system\ClassManager::getInstance('entity');
        $oRecord = $oEntity->model->selectByPK($this->nEntityFK);
        
        $sUrl = 'https://graph.facebook.com/'.$this->nFacebookId.'?fields=events.fields(picture,location,name,description,owner,venue,end_time,id,start_time)';
            
        $oFacebookAccount = \P\lib\framework\core\system\ClassManager::getInstance('facebook_account');
        $oFbRecord = $oFacebookAccount->model->selectByfacebookId($psFacebookId);
        
        
        if ($oFbRecord->type == 'pages')
        {
            $sUrl .= '&access_token='.$this->sToken;

            //utils\Debug::e($oFbRecord->type.' - '.$sUrl);
            
            $sFeed = file_get_contents($sUrl);
        }
        elseif($oFbRecord->type == 'profil' || $oFbRecord->type == 'friends')
        {
            $oLogin = \P\lib\framework\core\system\ClassManager::getInstance('login');
            $oRecord = $oLogin->model->load(2);
            
            $sToken =  $oLogin->model->token;
            
            $sUrl .= '&access_token='.$sToken;

           // utils\Debug::e($oFbRecord->type.' - '.$sUrl);
            
            $sFeed = file_get_contents($sUrl);
        }
        else
        {
            die('Type facen=book inconnu');
        }
        
        $this->oFeed        = json_decode($sFeed);
        $this->nFacebookId  = $this->oFeed->id;
        
      //  file_put_contents($sFilePath, serialize($this->oFeed));
    }
    
    
    public function loadGaleryFeedName($psFacebookId)
    {
        $sFilename = date('Y-m-d-H').'_galery_name_'.$this->nFacebookId.'azcd.txt';
        $sFilePath = \P\lib\framework\core\system\PathFinder::getTempDir().$sFilename;
        
        $oEntity = \P\lib\framework\core\system\ClassManager::getInstance('entity');
        $oRecord = $oEntity->model->selectByPK($this->nEntityFK);
        
        $sUrl = 'https://graph.facebook.com/'.$this->nFacebookId.'?fields=albums';
            
        $oFacebookAccount = \P\lib\framework\core\system\ClassManager::getInstance('facebook_account');
        $oFbRecord = $oFacebookAccount->model->selectByfacebookId($psFacebookId);
        
        if ($oFbRecord->type == 'pages')
        {
            $sUrl .= '&access_token='.$this->sToken;

            //utils\Debug::e('ALBUM NAME -- '.$oFbRecord->type.' - '.$sUrl);
            
            $sFeed = file_get_contents($sUrl);
        }
        elseif($oFbRecord->type == 'profil' || $oFbRecord->type == 'friends')
        {
            $oLogin = \P\lib\framework\core\system\ClassManager::getInstance('login');
            $oRecord = $oLogin->model->load(2);
            
            $sToken =  $oLogin->model->token;
            
            $sUrl .= '&access_token='.$sToken;

            //utils\Debug::e('ALBUM NAME -- '.$oFbRecord->type.' - '.$sUrl);
            
            $sFeed = file_get_contents($sUrl);
        }
        else
        {
            die('Type facen=book inconnu');
        }
        
        $this->oFeed        = json_decode($sFeed);
        $this->nFacebookId  = $this->oFeed->id;
        
      //  file_put_contents($sFilePath, serialize($this->oFeed));
    }
    
    
    public function _registerTree()
    {
        //utils\Debug::e($this->oFeed->feed->data);
                
        $asItems = array();
        foreach ($this->oFeed->feed->data as $oData)
        {
            $sStatus = $this->_getItem($oData);
            /*
            if ($sStatus === true)
                utils\Debug::e('Enregistré');
            else
                utils\Debug::e('perdu !'.$sStatus);*/
        }
                
        return $asItems;
    }
    
    
    private function _getItem($poData)
    {
        if ($poData->from->id != $this->oFeed->id) return 'Auteur invité';
        
        $sType      = $poData->type;
        $sMessage   = '';
        
        if (isset($poData->message))
            $sMessage   = $poData->message;

        switch($sType)
        {
            case 'photo':
                utils\Debug::e('photo');
                return new PhotoSimple($poData, $this);
                break;
                
            case 'video':
                utils\Debug::e('Video');
                return new Video($poData, $this);
                break;
            
            case 'link':
                utils\Debug::e('Link');
                return new Link($poData, $this);
                break;
            
            case 'status':
                utils\Debug::e('status');
                return new Status($poData, $this);
                return 'status';
        }
    }
}
?>
