<?php
namespace P\lib\framework\helpers;
use P\lib\framework\core\system as system;

// By convention, only those messages types are allowed.
define('MESSAGE_ERROR', 	'error');
define('MESSAGE_WARNING', 	'warning');
define('MESSAGE_NOTICE', 	'alert');
define('MESSAGE_SUCCESS', 	'success');


/**
 *
 * Static class used for soft messaging into the program
 * @author johan
 *
 */
class Message
{
    static $_asMessages;
    static $_asInstantMessages;
    
    public static function _message($psType, $psMessage, $psTitle='', $pbCloseable=true)
    {
        $theme = \P\lib\framework\themes\ThemeManager::load();
        
        $theme->message = new \stdClass();
        $theme->message->closeable    = $pbCloseable;
        $theme->message->title        = $psTitle;
        $theme->message->content      = $psMessage;
        
        switch($psType)
        {
            case 'error':
            case 'warning':
                return $theme->display('layout/messages/error.tpl.php');
            
            case 'success':
            case 'ok':
                return $theme->display('layout/messages/success.tpl.php');
            
            case 'alert':
                return $theme->display('layout/messages/alert.tpl.php');
            
            default :
            case 'info':
                return $theme->display('layout/messages/info.tpl.php');
            
        }
    }
    
    
    public static function setMessage($psMessage, $psType=MESSAGE_NOTICE)
    {
        $asMessage['messageContent']  	= $psMessage;
        $asMessage['messageType']     	= $psType;

        $_SESSION['message'][]          = $asMessage;
    }
    
    
    /**
     * Return all messages entered into the session
     * @param Boolean $pbSimple
     */
    public static function getMessage()
    {
        $sMessage = '';
        if (isset($_SESSION['message']) && !empty($_SESSION['message']))
        {
            foreach ($_SESSION['message'] as $asMessage)
            {
                $sMessage .= self::_message($asMessage['messageType'], $asMessage['messageContent']);
            }
            
            unset($_SESSION['message']);
            system\Session::destroy('message');
        }
        
        return $sMessage;
    }
    
    
    public static function getMessagePopup()
    {
        if (isset($_SESSION['message']) && !empty($_SESSION['message']))
        {
            $message = '';
            foreach ($_SESSION['message'] as $asMessage)
            {
                if ($asMessage['messageType'] == MESSAGE_ERROR)
                {
                    $message .= 'alert("'.\P\lib\framework\core\utils\String::escapeChar('"', $asMessage['messageContent']).'");';
                }
                else 
                {
                    $message .= 'smartadmin_message_success("'.\P\lib\framework\core\utils\String::escapeChar('"', $asMessage['messageContent']).'");';
                }
            }
            
            if (!empty($message))
            {
                JSManager::addInstructions('$(document).on("ready", function(){ '.$message.' });');
            }
            
            unset($_SESSION['message']);
            system\Session::destroy('message');
        }
    }
    
    
    public static function instantMessage($message, $psType=MESSAGE_NOTICE)
    {
        $asMessage = array();
        $asMessage['messageContent']            = $message;
        $asMessage['messageType']               = $psType;

        $asMessages                             = system\Session::get('instant_message', array());
        
        $asMessages[]                           = $asMessage;
        
//        \P\lib\framework\core\utils\Debug::e('ajouté');
        
        system\Session::set('instant_message', $asMessages);
    }
    
    
    public static function getInstantMessages()
    {
        $sMessages = '';
        
        if (!isset($_SESSION['instant_message']))
            return false;
        
        foreach ( $_SESSION['instant_message'] as $message)
        {
            if ($message['messageType'] == MESSAGE_ERROR)
            {
                $sMessages .= 'smartadmin_message_error("'.  \P\lib\framework\core\utils\String::escapeChar('"', $message['messageContent']).'");'."\n";
            }
            else
            {
                $sMessages .= 'smartadmin_message_success("'.  \P\lib\framework\core\utils\String::escapeChar('"', $message['messageContent']).'");'."\n";
            }
        }
        
        system\Session::destroy('instant_message');
        
        
        $sMessages = '$(document).ready(function(){ '.$sMessages.' });';
        
        
        return \P\tag('script', $sMessages);
    }
}
