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
    
    public static function _message($psType, $psMessage, $psTitle='', $pbCloseable=false)
    {
        switch($psType)
        {
            case 'error':
            case 'warning':
                $sAlertStyle = 'error';
                break;
            
            case 'success':
            case 'ok':
                $sAlertStyle = 'success';
                break;
            
            case 'alert':
                $sAlertStyle = 'block';
                break;
            
            default :
            case 'info':
                $sAlertStyle = 'info';
                break;
            
        }
        
        
        $sMessage    = '<div class="alert alert-'.$sAlertStyle.'">';
        
        if ($pbCloseable)
            $sMessage   .= '<a class="close" data-dismiss="alert" href="#">×</a>';
       
        if (!empty($psTitle))
            $sMessage   .= ' <h4 class="alert-heading">'.$psTitle.'</h4>';
        
        $sMessage  .= $psMessage;
        $sMessage  .= '</div>';
        
        
        return $sMessage;
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
}
