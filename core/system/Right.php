<?php
namespace P\lib\framework\core\system;

class Right
{
	
	/**
	 * Check the right for the controller
	 *
	 * @param Controller $poController
	 * @param String $psAction
	 */
    public static function ckeckRights($poController, $psAction)
    {
    	// On load les droits stockés en base
    	$asRolesDb = self::loadRights($poController, $psAction);
    	
    	// On surcharge les droits avec ceux spécifiés en "dur"
        $asRoles = $poController->getRoles(strtolower($psAction));
        
        return self::evalRights(array_unique(array_merge($asRolesDb, $asRoles)));
    }
    
    
    
    /**
     * Check if the user have the correct rights
     *
     * @param Array $pasRoles
     */
    public static function evalRights($pasRoles)
    {
        if (in_array(ROLE_ANONYMOUS, $pasRoles)) return true;
        
        if (in_array(ROLE_LOGGED, $pasRoles))
        {
            if (!Auth::isLogged())
            	Auth::login();
            else
            	return true;
        }
        
        if (!Auth::isLogged()) return false;
        
        foreach ($pasRoles as $nRole)
        {
        	if (Auth::haveRole($nRole)) return true;
        }
        
        return false;
    }
    
    
    /**
     * Load the rights stored in database
     *
     * @param Controller $poController
     * @param String $psAction
     */
    public static function loadRights($poController, $psAction)
    {
    	$oActionRights = ClassManager::getInstance(PathFinder::tableToController('action_right'));
    	
    	$asRights = $oActionRights->getRights($poController, $psAction);
    	
    	return $asRights;
    }
}
