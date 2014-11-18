<?php
namespace P\lib\framework\core\system;
use P\lib\framework\core\utils as utils;

class Auth
{
    static $_asRoles;
    
    /**
     * This static method can be called by any component to check if the user is logged
     * The Login controller use it to submit login informations and eventually log the user in
     */
    public static function logUser()
    {
        if (self::isLogged())
            return true;
            
        $sLogin     = utils\Http::getParam('login_login');
        $sPassword  = utils\Http::getParam('login_password');
            
        $oLogin = ClassManager::getInstance('App_Login_login');
        
        if ($oLogin->logUser($sLogin, $sPassword))
        {
            $_SESSION['login']              = $sLogin;
            $_SESSION['login_date']         = date('Y-m-d H:i:s');
            $_SESSION['loginpk']            = $oLogin->getUserId();
            $_SESSION['login_firstname']    = $oLogin->getFirstName();
            $_SESSION['login_lastname']     = $oLogin->getLastName();
            
            self::buildRoles(true);
            
            $_SESSION['login_roles']        = self::$_asRoles;
            
            return true;
        }
        
        return false;
    }
    
    
    /**
     * Init all the roles and cache them
     * If a re built is needed, $pbForceBuild must be set to true
     *
     * @param Boolean $pbForceBuild
     */
    public static function buildRoles($pbForceBuild=false)
    {
        if ($pbForceBuild)
        {
        	// TODO voir comment gérer le get Instance de classManager
            $oLoginRole 	= ClassManager::getInstance('App_LoginRole_LoginRole');
            
            $asRoles 		= $oLoginRole->getRolesByUserId(self::getUserId());
            
            foreach ($asRoles as $asRole)
            {
            	self::$_asRoles[] = $asRole['key'];
            }
            
        }
        
        // TODO gérer SESSION avec system\session
        if (!empty(self::$_asRoles)) return self::$_asRoles;
        
        if (isset($_SESSION['login_roles']) && !empty($_SESSION['login_roles']))
        {
           	self::$_asRoles[] = $_SESSION['login_roles'];
        }
            
        self::$_asRoles = array();
    }
    
    
    
    /**
     * Destroy the session and logout the user
     */
    public static function logout()
    {
        $_SESSION = array();
        
	    if (ini_get("session.use_cookies"))
	    {
	    	$params = session_get_cookie_params();
	    	setcookie(
	    			session_name(), '', time() - 42000,
	        		$params["path"], $params["domain"],
	        		$params["secure"], $params["httponly"]
	        		);
		}
	        
	    session_destroy();
    }

    
    /**
     *
     * Check if the user is successfully logged in
     */
    public static function isLogged()
    {
        if (isset($_SESSION['loginpk']))
            return true;
            
        return false;
    }


    public static function isJohan()
    {
        return self::isLogged() && ($_SESSION['loginpk'] == 1);
    }

    
    /**
     * Return the User ID
     */
    public static function getUserId()
    {
        if (self::isLogged())
            return $_SESSION['loginpk'];
            
        return false;
            
//        throw new ErrorException('User is not logged in');
    }
    
    
    /**
     * Fetch the role of the current user
     */
    public function getRoleByUserUid()
    {
    	$asRoles = self::getRoles();
    	
    	if (is_array($asRoles))
	    	return $asRoles[0];
	    	
		return false;
    }
    
    
    /**
     * Returns the full name of the user (if the information is stored in the database)
     *
     * @throws ErrorException
     */
    public static function getUserName()
    {
        if (self::isLogged())
            return $_SESSION['login_firstname'].' '.strtoupper($_SESSION['login_lastname']);
            
        throw new \ErrorException('User is not logged in');
    }

    
    /**
     * Checks if the current user have the role $psRole
     *
     * @param String $psRole
     */
    public static function haveRole($psRole='')
    {
        $pnRole = (int) $psRole;
        
        if (empty($pnRole)) return false;
        if (!self::isLogged()) return false;

        if (empty(self::$_asRoles)) self::buildRoles(true);
        
        if (in_array($pnRole, self::$_asRoles))
        {
        	return true;
        }
        else
        {
        	//Message::setMessage('Droits utilisateurs insuffisants', MESSAGE_ERROR);
        	
        	return false;
        }
    }
    
    /**
     *
     * Shortcut for displaying a forbidden access page
     */
    public static function forbidden()
    {
    	//header('HTTP/1.1 403 Forbidden');
        $oResponse = ClassManager::getInstance('App_Login_Login')->forbidden();
	    
	    ObjectPublisher::display('', $oResponse);
    }
    
    
    /**
     *
     * Shortcut for displaying an error 404 (not found) page
     */
    public static function error404()
    {
    	header("HTTP/1.1 404 Not Found");
        
        $oTheme = \P\lib\framework\themes\ThemeManager::load();
        $oTheme->meta_title = 'Page non trouvée';
        $oTheme->meta_description = 'Page non trouvée';
        
        return $oTheme->display('error404.tpl.php');
    }
    
    
    /**
     *
     * Displays the login box
     * This shortcut is used by the Login Controller
     */
    public static function login()
    {
        $oResponse = ClassManager::getInstance('App_Login_Login')->login();
	    
        $sView = PathFinder::getViewDir(PathFinder::tableToController('login')).'/login.php';
        
	    ObjectPublisher::display($sView, $oResponse);
    }
    
    
    /**
     *
     * Return the cached content of $_asRoles.
     * If the array is truncate, the roles are built
     */
    public static function getRoles()
    {
    	if (empty(self::$_asRoles)) self::buildRoles(true);
    	
    	return self::$_asRoles;
    }
    
    
    /**
     * Returns the available(s) service(s) for the current user
     */
    public static function getServices()
    {
    	$asRoles 		= self::buildRoles(true);
    	$oRoleService 	= ClassManager::getInstance(PathFinder::tableToController('role_service'));
    	
    	return $oRoleService->getServicesByRole($asRoles);
    }
    
    
}
