<?php
namespace P\lib\framework\core\system\traits;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

trait importFromFile
{
    protected $config = array();
    protected $_importFromFile_debug;
    public $count = 0;
    
    public function import()
    {
        $this->theme->importfomfile_fieldName = strtolower(system\PathFinder::namespaceToTable(__CLASS__));
        $this->theme->importfromfile_fieldName = strtolower(system\PathFinder::namespaceToTable(__CLASS__));
        
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
                $this->theme->importfomfile_message = helpers\Message::_message('error', 'erreur de check config');
            }
            else
            {
                try 
                {
                    $sCsvFileName = $this->_getCsvContent( system\PathFinder::namespaceToTable(__CLASS__), $this->theme->importfomfile_fieldName);
                }
                catch(\Exception $e)
                {
                    helpers\Message::setMessage($e->getMessage(), MESSAGE_ERROR);
                    $this->theme->importfomfile_message = helpers\Message::_message('error', $e->getMessage());
                    utils\Http::redirect(\P\url());
                }
                
                $i = 1;
                if ($bSkipFirstLine)
                    $i = 0;

                if (empty($sCsvFileName))
                {
                    echo '$sCsvFileName est vide';
                    return false;
                }
                if (($handle = fopen($sCsvFileName, 'r')) === FALSE)
                {
                    echo 'ouverture du fichier impossible'; return false;
                }

                while ($asLine = fgetcsv($handle, 4000, $sSeparator))
                {
                    if ($i > 0)// && $i <= 1)
                    {
                        $this->count++;
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
            if (!move_uploaded_file($_FILES[$psFieldName]['tmp_name'], $sFilePath))
            {
                throw new \ErrorException('Aucun fichier uploadé - echec de moveuploadedfile');
            }

            $file = file_get_contents($sFilePath);
            $file = iconv(mb_detect_encoding($file, mb_detect_order(), true), "UTF-8", $file);

            if ($file)
            {
                file_put_contents($sFilePath, $file);
            }
            else
            {
                die('LINE::'.__LINE__.' File not converted');
            }
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
