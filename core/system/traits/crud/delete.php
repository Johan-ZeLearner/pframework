<?php
namespace P\lib\framework\core\system\traits\crud;
use P\lib\framework\core\utils as utils;

trait delete
{
    public function delete()
    {
        $nKey = $this->getKey();
        
        if ($this->deleteByPK($nKey))
        {
            utils\Http::redirect($this->_deleteGetRedirect($nKey));
        }
    }
    
    
    
    public function deleteByPK($pnKey)
    {
        if (!$this->_deleteCustom($pnKey))
        {
            \P\lib\framework\helpers\Message::setMessage('La suppression des dépendances a échouée');
            utils\Http::redirect($this->_deleteGetRedirect($pnKey));
        }
        
        return $this->model->delete($pnKey);
    }
    
    
    protected function _deleteGetRedirect($pnPK=0)
    {
        return \P\url('', 'index');
    }
    
    
    protected function _deleteCustom($pnPK=0)
    {
        return true;
    }
    
    
}