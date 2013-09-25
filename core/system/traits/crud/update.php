<?php
namespace P\lib\framework\core\system\traits\crud;
use P\lib\framework\helpers\form as form;
use P\lib\framework\core\utils as utils;

trait update
{
    public function update()
    {
        /**
         * @var form\Form
         */
        $nPK = (int) utils\Http::getParam('key');
        
        $oForm = $this->model->toForm($nPK);
        // calling the hook for editing the form
        $this->_editUpdateForm($oForm);

        
        // checking the form
        $this->_saveUpdate($oForm, $nPK);
        
        $oTheme = \P\lib\framework\themes\ThemeManager::load();
        
        // We assign the vars
        $oTheme->form       = $oForm;
        $oTheme->title      = $this->_getTitle('update');
        
        $this->_handleEditForm($oTheme->form);
        
        return $oTheme->display($this->getUpdateTemplate());
    }
    
    
    
    protected function _handleEditForm($poForm)
    {
        
    }
    
    
    public function getUpdateTemplate()
    {
        return 'trait_create_create.tpl.php';
    }
    
    /**
     * Hook function to add / remove information to the current $poForm
     * 
     * @param \P\lib\framework\helpers\form\Form $poForm
     * @return \P\lib\framework\helpers\form\Form
     */
    protected function _editUpdateForm(form\Form $poForm)
    {
        return $poForm;
    }
    
    
    protected function _saveUpdate(form\Form $poForm, $pnPK=0)
    {
        if (utils\Http::isPosted())
        {
            if ($poForm->isValid() && $this->updateCheckValues($poForm))
            {
               $nPK = $poForm->save($pnPK);

               if ($nPK != null && $this->_saveUpdateCustom($nPK))
               {
                   \P\lib\framework\helpers\Message::setMessage(constant('EDIT_OK_'.strtoupper($this->model->getTable())), MESSAGE_SUCCESS);
                   utils\Http::redirect($this->_updateGetRedirect($nPK));
               }
            }

            \P\lib\framework\helpers\Message::setMessage(constant('EDIT_KO_'.strtoupper($this->_oDal->getTable())), MESSAGE_ERROR);
        }
    }
    
    
    /**
     * Alias
     */
    public function edit()
    {
        return $this->update();
    }
    
    
    protected function _saveUpdateCustom($pnPK)
    {
        return true;
    }
    
    public function updateCheckValues()
    {
        return true;
    }
    
    
    
    protected function _updateGetRedirect($pnPK)
    {
        return \P\url('', 'index');
    }
}