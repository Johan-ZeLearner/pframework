<?php
namespace P\lib\framework\core\system\traits;
use P\lib\framework\core\system as system;

trait ManageRights
{
    public function checkRights($psAction)
    {
        if (in_array($psAction, $this->manageRights_getFreeAccess()))
        {
            return true;
        }

        $nUserFK = system\Session::get(SESSION_NAME);

        if ($nUserFK > 0)
        {
            $oRights = system\ClassManager::getInstance('rights');
            
            if (is_object($oRights))
            {
                return $oRights->checkAccessByUser($this->model->_table, $psAction, $nUserFK);
            }
        }
        else 
        {
            system\Session::set('REDIRECT_URL', \P\url()->__toString());
            \P\lib\framework\core\utils\Http::redirect(\P\url('member', 'login'));
        }
        
        return false;
    }
    
    public function manageRights_getFreeAccess()
    {
        return array();
    }
}
?>
