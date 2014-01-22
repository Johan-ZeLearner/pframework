<?php
namespace P\lib\framework\core\system\traits;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

trait importFromFileAdvanced
{
    use importFromFile;

    public $importfromfile_fields_normal    = array();
    public $importfromfile_fields_special   = array();
    public $importfromfile_fields_all       = array();
    public $importfromfile_save_normal      = array();
    public $importfromfile_save_special     = array();
    public  $_fields;
    
    public function import()
    {
        $this->importFromFile_init();
        return $this->_import();
    }
    
    
    public function importFromFile_init()
    {
        $this->registerClass(new classes\ImportAdvanced($this->model));
    }
    
    
    public function _import()
    {
        $this->theme->importfromfile_advanced = true;
        
        $nStep = \P\get('step', 1);
        
        if ($nStep == 1 && utils\Http::isPosted())
            $nStep = 2;
        
        $this->theme->importfromfile_fieldName = strtolower(system\PathFinder::namespaceToTable(__CLASS__));
        $this->theme->importfomfile_fieldName = strtolower(system\PathFinder::namespaceToTable(__CLASS__));
        switch ($nStep)
        {
            case 2:
                return $this->_importFromFile_handleAssociation();
                break;
            
            case 3:
                return $this->_importFromFile_handleSave();
                break;
            
            default:
            case 1:
                return $this->_importFromFile_getOutput();
                break;
        }
    }
    
    
    /**
     * On sauve le fichier à importer, et on propose l'association des champs
     */
    private function _importFromFile_handleAssociation()
    {
        $this->_importFromFile_getCsvHandle();
        
        return $this->theme->display('trait_importFromFileAdvanced_association.tpl.php');
    }
    
    
    private function _importFromFile_handleSave()
    {
        // on sauve l'association Colonnes / Champs
        $this->_importFromFile_saveFieldAssociation();
        
        return $this->theme->display('trait_importFromFileAdvanced_save.tpl.php');
    }
    
    
    private function _importFromFile_getCsvHandle()
    {
        $this->theme->importfromfile_fields        = $this->_importfromfile_getFields();

        // récupération du csv
        try
        {
            $sCsvFileName = $this->_getCsvContent( system\PathFinder::namespaceToTable(__CLASS__), $this->theme->importfromfile_fieldName);
            
//            utils\Debug::e($sCsvFileName);
            $file = new \SplFileObject($sCsvFileName);
            
            $this->theme->importfromfile_separator = $file->getCsvControl()[0];
            
            if ($this->theme->importfromfile_separator == ',')
            {
                $f1 = fopen($sCsvFileName, "r");
                
                $sContent = fgets($f1, 10000);
                
                $nCountComma    = substr_count($sContent, ",");
                $nCountTab      = substr_count($sContent, "\t");
                
                if ($nCountTab > $nCountComma)
                    $this->theme->importfromfile_separator = "\t";
                
                fclose($f1);
                
            }
            
        }
        catch(\Exception $e)
        {
            helpers\Message::setMessage($e->getMessage(), MESSAGE_WARNING);
            utils\Http::redirect(\P\url());
            die();
        }
        
        if (($handle = fopen($sCsvFileName, 'r')) === FALSE)
        {
            helpers\Message::setMessage('Erreur de lecture du fichier CSV', MESSAGE_WARNING);
            utils\Http::redirect(\P\url());
            die();
        }

        $this->theme->csv = $handle;
    }
    
    
    private function _importfromfile_getFields()
    {
        return array_merge($this->_importfromfile_getFields_normal(), $this->_importfromfile_getFields_special());
    }
    
    
    private function _importfromfile_getFields_normal()
    {
        $asFields = $this->model->getFieldNames();
        
        foreach ($asFields as $nKey => $sName)
        {
            $this->importfromfile_fields_normal[$sName] = $sName;
        }
        
        return $this->importfromfile_fields_normal;
    }
    
    
    private function _importfromfile_getFields_special()
    {
       return array(); 
    }
    
    
    public function importAjax()
    {
        $sAjax = \P\get('ajax');
        
        $this->theme->importfromfile_fieldName = strtolower(system\PathFinder::namespaceToTable(__CLASS__));
        $this->_importFromFile_getCsvHandle();
        
        switch($sAjax)
        {
            case 'save':
                return $this->_importFromFile_ajaxSave();
                break;
        }
    }
    
    
    private function _importFromFile_ajaxSave()
    {
        \P\lib\framework\themes\ThemeManager::setAjax();
        header('content-type: text/html');
        
        $from                                   = \P\get('start', 0);
        $length                                 = \P\get('length', 10);
        $this->ignore_first_line                = (bool) system\Session::get('ignore_first_line', 0);
        
        if ($from > 20000) return '';
        
        $sAssociation = system\Session::get($this->theme->importfromfile_fieldName);
        
        $this->_fields = array();
        if (!empty($sAssociation))
            $this->_fields = unserialize($sAssociation);
        else
            return '<tr><td colspan="2">'.helpers\Message::_message (MESSAGE_ERROR, 'Aucun champ n\' a été associé').'</td></tr>';

        $this->importfromfile_fields_all = $this->_importfromfile_getFields();
        
        
        $this->j = 0;
        $bTrigged=false;
        while ($asLine = fgetcsv($this->theme->csv, 10000, $this->theme->importfromfile_separator))
        {
            $i = 0;
            
            if ($this->ignore_first_line && $this->j == 0)
            {
                $this->j++;
                continue;
            }
            
            if ($this->j >= $from && $this->j <= ($from + $length) && $this->_importFromFile_checkExisting($asLine))
            {
                if (!$bTrigged)
                {
                    echo $this->_message('DEBUT (j = '.$this->j.')', $this->j, 'success');
                    $bTrigged = true;
                }
                
                $this->importfromfile_save_normal   = array();
                $this->importfromfile_save_special  = array();
                
                $this->_importFromFile_initBeforeLine();
                
                foreach ($asLine as $sValue)
                {
                    $this->_importFromFile_checkColValue($sValue, $i);
                    $i++;
                }
                
                $this->_importFromFile_saveData();
            }
            
//            echo '<tr><td colspan="2">'.$this->j++.'</td></tr>';
            
            if ($this->j > ($from + $length))
                die($this->_message('FIN (j = '.$this->j.')', $this->j, 'important'));
            
            $this->j++;
        }
    }
    
    private function _importFromFile_checkExisting($asLine)
    {
        return true;
    }
    
    
    public function _message($message, $psTitle='', $badge='')
    {
        $sBadge = '';
        if (!empty($badge))
            $sBadge = 'badge badge-'.$badge;
        
        return '<tr><td>'.$psTitle.'</td><td><span class="'.$sBadge.'">'.$message.'</span></td></tr>';
    }
    
    
    public function _importFromFile_initBeforeLine()
    {
        return true;
    }
    
    
    protected function _importFromFile_checkColValue($sValue, $i)
    {
        $sColName = $this->_fields['col_'.$i];
        
         // si le champ est mappé à une colonne PRODUCT
        if (key_exists($sColName, $this->importfromfile_fields_normal))
        {
            if ($this->_importFromFile_checkValue($sColName, $sValue))
                $this->importfromfile_save_normal[$sColName] = $this->_importFromFile_escapeValue($sColName, $sValue);
        }
        
        $this->_importFromFile_checkColSpecial($sColName, $sValue);
    }
    
    
    protected function _importFromFile_checkSaveData()
    {
        $sPrimary = $this->model->getPrimary();
        
        if (isset($this->importfromfile_save_normal[$sPrimary]))
        {
            return true;
        }
    }
    
    
    protected function _importFromFile_checkColSpecial($sColName, $sValue)
    {
        return true;
    }
    
    protected function _importFromFile_escapeValue($sColName, $sValue)
    {
        return $sValue;
    }
    
    
    protected function _importFromFile_checkValue($sColName, $sValue)
    {
        return true;
    }
    
    
    protected function _importFromFile_saveData()
    {
        $sPrimary = $this->model->getPrimary();
        $nId        = $this->importfromfile_save_normal[$sPrimary];
        
        $this->model->save($this->importfromfile_save_normal, $nId);
    }
    
    
    private function _importFromFile_saveFieldAssociation()
    {
        $this->_importFromFile_getCsvHandle();
        
        while ($asLine = fgetcsv($this->theme->csv, 10000, $this->theme->importfromfile_separator))
        {
            $count = count($asLine);
            break;
        }
        
        $asData = array();
        for($i=0; $i < $count; $i++)
        {
            $asData['col_'.$i] = \P\get('col_'.$i, '');
        }
        
        system\Session::set('ignore_first_line', (bool) \P\get('ignore', 0));
        
        system\Session::set($this->theme->importfromfile_fieldName, serialize($asData));
    }
    
}