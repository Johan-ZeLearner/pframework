<?php
namespace P\lib\framework\core\system\traits;
use P\lib\framework\core\system as system;
use P\lib\framework\core\utils\Debug;

trait ManageRights
{
    public function checkRights($psAction)
    {
//        die();
        if (in_array($psAction, $this->manageRights_getFreeAccess()))
        {
//            die('OK');
            return true;
        }

        $nUserFK = system\Session::get(SESSION_NAME);

        if ($nUserFK > 0)
        {
            $oRights = system\ClassManager::getInstance('rights');
            
            if (isset($this->model->_table))
            {
                return $oRights->checkAccessByUser($this->model->_table, $psAction, $nUserFK);
            }
            else
            {
                return true; //system\Auth::isLogged();
            }
        }
        else 
        {
            system\Session::set('REDIRECT_URL', \P\url()->__toString());
            \P\lib\framework\core\utils\Http::redirect(\P\url('employee', 'login'));
        }
        
        return false;
    }
    
    public function manageRights_getFreeAccess()
    {
        return array();
    }
}
?>
