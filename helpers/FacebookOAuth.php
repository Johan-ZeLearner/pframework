<?php
namespace P\lib\framework\helpers;
use P\lib\framework\core\system as  system;
use P\lib\framework\core\utils as   utils;

/**
 * Description of FacebookOAuth
 *
 * @author Johan
 */
class FacebookOAuth 
{
    const APP_ID        = 364185360326491;
    const APP_SECRET    = '52df1aaa2812d7860b4b429af615c788';
    const URL           = 'http://www.alpes-passion.fr/';
    static $token;         

    public static function getCurentUrl()
    {
        return urlencode(self::URL);
    }

    
    public static function getAuthLink()
    {
        $sSeed = system\Session::get('facebook_state');
        
        system\Session::set('login_redirection', \P\url());
        
        if (empty($sSeed))
        {
            $sSeed =  rand(0,  time()).'_seed';
            system\Session::set('facebook_state', $sSeed);
        }
        
        $sFaceBookUrl = 'https://www.facebook.com/dialog/oauth?';
        $sFaceBookUrl .= 'client_id='.self::APP_ID;
        $sFaceBookUrl .= '&redirect_uri='.self::getCurentUrl();
        $sFaceBookUrl .= '&scope=publish_stream,read_stream,read_friendlists,manage_pages,user_birthday,email,publish_actions,friends_photos,user_photos,user_likes';
        $sFaceBookUrl .= '&state='.$sSeed;
        
        return $sFaceBookUrl;
    }
    
    
    public static function handleOAuth()
    {
        system\Session::destroy('facebook_state');
        system\Session::destroy('my_url');
        
        $sCode      = utils\Http::getParam('code');
        $sState     = utils\Http::getParam('state');
        
        if (!empty($sCode) && !empty($sState) )
        {
            return self::processOAuth($sCode, $sState);
        }
        
        return false;
    }
    
    
    public static function processOAuth($sCode, $sState)
    {
        $sState = str_replace('#_=_', '', $sState);
        
        if (empty($sState) || empty($sCode))
            return false;
        
        // Access token
        $sAccessUrl = 'https://graph.facebook.com/oauth/access_token?';
        $sAccessUrl .= 'client_id='.self::APP_ID;
        $sAccessUrl .= '&redirect_uri='.self::getCurentUrl();
        $sAccessUrl .= '&client_secret='.self::APP_SECRET;
        $sAccessUrl .= '&code='.$sCode;
        
        $sResponse = file_get_contents($sAccessUrl);
        
        if (preg_match('/access_token=([0-9a-zA-Z]*)(&expires=([0-9]*))*/', $sResponse, $asReq))
        {
            $sAccessToken   = $asReq[1];
            $nExpire        = (int) $asReq[2];
            
            if (empty($nExpire))
                $nExpire = 60 * 24 * 3600;
            
            // get basic information
            $sInfo = file_get_contents('https://graph.facebook.com/me?access_token='.$sAccessToken);
            
            $asInfo = json_decode($sInfo);
            
            $asData = array(
                'loginpk'       => 0,
                'facebook_id'   => $asInfo->id,
                'name'          => $asInfo->name,
                'firstname'     => $asInfo->first_name,
                'middlename'    => $asInfo->middle_name,
                'lastname'      => $asInfo->last_name,
                'profile'       => $asInfo->link,
                'username'      => $asInfo->username,
                'birthday'      => $asInfo->birthday,
                'gender'        => $asInfo->gender,
                'email'         => $asInfo->email,
                'updated_time'  => $asInfo->updated_time,
                'expire'        => $nExpire,
                'token'         => $sAccessToken
            );
            
            return $asData;
        }
        else
        {
            utils\Debug::dump('pas de match ');
            utils\Debug::dump($sResponse);
        }
        
        return false;
    }
    
    
    public static function logout()
    {
        $_SESSION = array();

        if (ini_get("session.use_cookies"))
        {
            $params         = session_get_cookie_params();
            
            setcookie(
                            session_name(), '', time() - 42000,
                            $params["path"], $params["domain"],
                            $params["secure"], $params["httponly"]
                            );
        }

        session_destroy();
        
        system\Session::destroy('userid');
        system\Session::destroy('token');
    }
    

    
    public static function getAppToken()
    {
        if (empty(self::$token))
        {
            $sUrl = 'https://graph.facebook.com/oauth/access_token?';
            $sUrl .= 'client_id='.self::APP_ID;
            $sUrl .= '&client_secret='.self::APP_SECRET;
            $sUrl .= '&grant_type=client_credentials';

            $sQueryString = file_get_contents($sUrl);

            self::$token = str_replace('access_token=', '', $sQueryString);
        }
        
        return self::$token;
    }
}

?>
