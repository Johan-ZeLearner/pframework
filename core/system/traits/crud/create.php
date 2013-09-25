<?php
namespace P\lib\framework\core\system\traits\crud;
use P\lib\framework\helpers\form as form;
use P\lib\framework\core\utils as utils;

trait create
{
    public function create()
    {
        /**
         * @var form\Form
         */
        $oForm = $this->model->toForm();
        
        // calling the hook for editing the form
        $this->_editCreateForm($oForm);
        
        // checking the form
        $this->_saveCreate($oForm);
        
        $oTheme = \P\lib\framework\themes\ThemeManager::load();
        
        // We assign the vars
        $oTheme->form   = $oForm;
        $oTheme->title  = $this->_getTitle('create');
        
        $this->_handleCreateForm($oTheme->form);
        
        return $oTheme->display($this->getCreateTemplate());
    }
    
    
    public function getCreateTemplate()
    {
        return 'trait_create_create.tpl.php';
    }
    
    
    /**
     * Hook function to add / remove information to the current $poForm
     * 
     * @param \P\lib\framework\helpers\form\Form $poForm
     * @return \P\lib\framework\helpers\form\Form
     */
    protected function _editCreateForm(form\Form $poForm)
    {
        return $poForm;
    }
    
    
    protected function _handleCreateForm(form\Form $poForm)
    {
    }
    
    
    protected function _saveCreate(form\Form $poForm)
    {
        if (utils\Http::isPosted())
        {
            if ($poForm->isValid() && $this->createCheckValues($poForm))
            {
               $nPK = $poForm->save();

               if ($nPK != null)
               {
                   \P\lib\framework\helpers\Message::setMessage(constant('ADD_OK_'.strtoupper($this->model->getTable())), MESSAGE_SUCCESS);
                   utils\Http::redirect($this->_createGetRedirect($nPK));
               }
            }

            \P\lib\framework\helpers\Message::setMessage(constant('ADD_KO_'.strtoupper($this->_oDal->getTable())), MESSAGE_ERROR);
        }
    }
    
    /**
     * Alias
     */
    public function add()
    {
        return $this->create();
    }
    
    
    public function createCheckValues($poForm)
    {
        return true;
    }
    
    
    protected function _createGetRedirect($pnPK)
    {
        return \P\url('', 'index');
    }
}