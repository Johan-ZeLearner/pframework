<?php
namespace P\lib\framework\core\system\traits;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

trait importFromFile
{
    protected $config = array();
    protected $_importFromFile_debug;
    
    public function import()
    {
        $this->theme->fieldName = strtolower(system\PathFinder::namespaceToTable(__CLASS__));
        
        $this->_importFromFile_customConfig();
        
        if (utils\Http::isPosted())
        {
            $this->_importFromFile_getConfig();
            
            $sSeparator     = utils\Http::getParam('separator', ';');
            $bSkipFirstLine = (bool) utils\Http::getParam('skip', 0);
            
            if ($sSeparator == '\t')
            {
                $sSeparator = "\t";
            }
            
            
            if (!$this->_importFromFile_checkConfig())
            {
                $this->theme->message = helpers\Message::_message('error', 'erreur de check config');
            }
            else
            {
                try 
                {
                    $sCsvFileName = $this->_getCsvContent( system\PathFinder::namespaceToTable(__CLASS__), $this->theme->fieldName);
                }
                catch(\Exception $e)
                {
                    $this->theme->message = helpers\Message::_message('error', $e->getMessage());
                }
                
                $i = 1;
                if ($bSkipFirstLine)
                    $i = 0;

                if (($handle = fopen($sCsvFileName, 'r')) === FALSE) return false;

                while ($asLine = fgetcsv($handle, 4000, $sSeparator))
                {
                    if ($i > 0)// && $i <= 1)
                    {
                        $bOk = $this->_importFromFile_processData($asLine, $i);
                    
                        if (!$bOk)
                        {
                            utils\Debug::e($this->_importFromFile_debug);
                            break;
                        }
                    }

                    $i++;
                }
            }
        }
        
        return $this->_importFromFile_getOutput();
    }
    
    
    protected function _importFromFile_getOutput()
    {
        return $this->theme->display($this->_importFromFile_getTemplate());
    }
    
    
    protected function _getCsvContent($psFilePrefix, $psFieldName='file')
    {
        $sFilename  = 'import_'.$psFilePrefix.'.csv';
        $sFilePath  = system\PathFinder::getTempDir().$sFilename;

        if (isset($_FILES[$psFieldName]['tmp_name']) && !empty($_FILES[$psFieldName]['tmp_name']))
        {
            move_uploaded_file($_FILES[$psFieldName]['tmp_name'], $sFilePath);
        }
        elseif (!is_file($sFilePath))
        {
            throw new \ErrorException('Aucun fichier uploadé');
        }
        

        return $sFilePath;
    }
    
    
    protected function _importFromFile_getTemplate()
    {
        return 'trait_importFromFile.tpl.php';
    }
    
    
    protected function _importFromFile_processData($pasData, $i)
    {
        return false;
    }
    
    
    protected function _importFromFile_getConfig()
    {
        return true;
    }
    
    
    protected function _importFromFile_checkConfig()
    {
        return true;
    }
    
    
    protected function _importFromFile_customConfig()
    {
        return true;
    }
}

?>
