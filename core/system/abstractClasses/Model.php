<?php
namespace P\lib\framework\core\system\abstractClasses;
use P\lib\framework\core\system as system;
use P\lib\framework\core\system\dal as dal;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers\form as form;

abstract class Model
{
    public 	$_scheme;
    public 	$haltNoResults  = false;
    protected 	$_table;
    protected 	$_infos;
    protected 	$_bGenerated    = false;
    protected 	$_browsable	= array();
    public      $dal;

    
    public function changeDal($psIndex, $pasArgs=array())
    {
        $this->dal = dal\DBHandler::$handler->getDB($psIndex, $pasArgs);
    }
    
    
    /**
        * Configure the DAL to use the $psTableName table
        *
        * @param String $psTableName
        * @throws ErrorException
    */
    public function __construct($pnDbNumber=0)
    {
        $this->_scheme 		= new \stdClass();
        $this->dal              = dal\DBHandler::$handler->getDB($pnDbNumber); // get Default DB

        if (empty($this->_table)) return false; // throw new \ErrorException('You must specify a table name');

        if (is_object($this->dal))
            $sSchemeClassName 	= $this->dal->getSchemeClassName(get_called_class(), $this->_table);
        else 
        {
            throw new \ErrorException('Erreur, pas objet !!! ');
            die();
        }
        
        $sConstantFile          = system\PathFinder::getConstDir(get_called_class()).$this->_table.'.php';
        $sInstructionsFile 	= system\PathFinder::getInstructionsDir(get_called_class()).$this->_table.'.php';

        if (is_file($sConstantFile))        require_once $sConstantFile;
        if (is_file($sInstructionsFile))    require_once $sInstructionsFile;

        if (system\Settings::getParam('environement', 'force_scheme_creation'))
        {
                // On génère le schema
                system\dal\SchemeCreator::createDbScheme($sSchemeClassName, $this->_table, $this);
                $this->_bGenerated = true;
        }

        try
        {
            // On essaye de charger le schema de la table
            $oScheme = new $sSchemeClassName();
            
            $this->_scheme = $oScheme->getScheme();
            $this->_infos = $oScheme->_infos;
        }
        catch (\Exception $e)
        {
            if ($this->_bGenerated)
            {
                utils\Debug::dump($e->getMessage());
                throw new \ErrorException('The generation seems to have failed. Program halted');
            }

            // On génère le schema
            //$this->_createDbSchema($sSchemeClassName);
            system\dal\SchemeCreator::createDbScheme($sSchemeClassName, $this->_table, $this);
        }
    }
    
    
    public function __get($psName)
    {
       $oField = $this->getField($psName);
        
       return $oField->value;
    }
    
    
    /**
        * Methode permettant de configurer specifiquement certains champs de la base de donnée
        */
    protected function _customFields()
    {
        return array();
    }


    /**
        * Execute _customFields();
        */
    public function customFields()
    {
        return $this->_customFields();
    }


    /**
        * Retourne le nom des champs de la table du Dal en cours
        * Peut être filtré avec les valeurs suivants :
        *  - all (tous les champs)  DEFAULT
        *  - readable (seulement les champs lisibles)
        *  - writable (seulement les champs pouvant etre ecrits)
        *  - browsable (seulement les champs listables)
        *
        * @param String $psFilter
        */
    public function getFieldNames($psFilter='all', $pbDebug=false)
    {
            $asFields = array();

            foreach ($this->_scheme as $sName =>$oField)
            {
                    if ($psFilter == 'readable')
                    {
                        if ($oField->readable)
                                $asFields[] = $sName;

                    }
                    elseif($psFilter == 'writable')
                    {
                            if ($oField->writable)
                                    $asFields[] = $sName;
                    }
                    elseif($psFilter == 'browsable')
                    {
                            if (!empty($this->_browsable))
                            {
                                    if (in_array($sName, $this->_browsable))
                                    {
                                            $nIndice = array_search($sName, $this->_browsable);
                                            $asFields[$nIndice] = $sName;
                                    }
                            }
                            else
                            {
                                if ($oField->browsable)
                                    $asFields[] = $sName;
                            }
                    }
                    else
                    {
                            $asFields[] = $sName;
                    }
            }

            ksort($asFields);

            if ($pbDebug)
                    utils\Debug::dump($this->_scheme);

            if ($pbDebug)
                    utils\Debug::dump($asFields);

            return $asFields;
    }

    
    public function getField($psName)
    {
        if (isset($this->_scheme->$psName))
        {
            return $this->_scheme->$psName;
        }
        
        throw new \ErrorException('The field '.$psName.' doesn\'t exists in this scheme');
    }
    

    /**
        * Retourne le nom des champs de la table du Dal en cours
        * Peut être filtré avec les valeurs suivants :
        *  - all (tous les champs)  DEFAULT
        *  - readable (seulement les champs lisibles)
        *  - writable (seulement les champs pouvant etre ecrits)
        *  - browsable (seulement les champs listables)
        *
        * @param String $psFilter
        */
    public function getFieldLabels($psFilter='all')
    {
        if (!in_array($psFilter, array('all', 'readable', 'writable', 'browsable'))) throw new \ErrorException('$psFilter must be "all", "readable", "writable" or "browsable"');

        if ($psFilter == 'all')
                $psFilter = '';

        $asFields = array();

        foreach ($this->_scheme as $sName => $oField)
        {
            if (!empty($psFilter))
            {
                if ($psFilter == 'browsable' && !empty($this->_browsable))
                {
                    if (in_array($sName, $this->_browsable))
                    {
                            $nIndice            = array_search($sName, $this->_browsable);
                            $asFields[$nIndice] = $oField->label;
                    }
                    else
                    {
                            //dump("champ inconnu => ".$sName);
                    }
                }
                elseif ($oField->$psFilter)
                {
                    $asFields[] = $oField->label;
                }
                // else we ignore the field
            }
            else
            {
                    $asFields[] = $oField->label;
            }
        }

        ksort($asFields);

        return $asFields;
    }


    /**
        * Return the scheme
        */
    public function getScheme()
    {
        return $this->_scheme;
    }


    /**
        *
        * Returns the name of the current table.
        * Useful for component-like request to the Dal
        */
    public function getTable()
    {
            return $this->_table;
    }


    /**
        *
        * Returns the name of the field primary key
        */
    public function getPrimary()
    {
        if (isset($this->_infos['primary']))
            return $this->_infos['primary'];
        else
            return $this->_table.'pk';
    }


    /**
        * Returns the value of the primary field
        */
    public function getPrimaryValue()
    {
            $sPrimaryName = $this->getPrimary();

            return $this->_scheme->$sPrimaryName->value;
    }


    /**
        * Load the values of the $_scheme from the Primary Key value of its record
        *
        * @param Integer $pnPK
        */
    public function load($pnPK, $pbDebug=false)
    {
        $pnPK 	 = (int) $pnPK;

        $bLoaded = false;
        if ($pnPK > 0)
        {
            $oFields = $this->selectByPK($pnPK, $pbDebug, false);

            if (!is_object($oFields)) return false;

            foreach ($oFields as $sFieldName => $sFieldValue)
            {
                if (isset($this->_scheme->$sFieldName))
                {
                    $bLoaded = true;

                    $this->_scheme->$sFieldName->value = stripslashes($sFieldValue);
                }
                //else
                    //utils\Debug::dump($sFieldName.' is not set');
            }
        }

        return $bLoaded;
    }


    /**
        * Shortcut to $this->select() for the search on the primary key exclusively.
        * Return a dbResponse object.
        *
        * @param Integer $pnPK
        */
    public function selectByPK($pnPK, $pbDebug=false, $pbRender=false)
    {
        
        if (is_object($pnPK))
        {
            utils\Debug::e(debug_backtrace());
            die();
        }   
        $oSelect = new dal\Select($this);
        $oSelect->andWhere($this->getPrimary().'='.$pnPK);
        $oSelect->render = $pbRender;
        
//        utils\Debug::e($oSelect->__toString());
        
        return $oSelect->fetchOne();
    }


    /**
        *  Generic method for selection row(s) depending of the where clause
        *
        * @param Array $pasFields
        * @param String $psWhere
        * @param Array $pasLimit
        */
    public function select($pasFields='', $psWhere='', $pasLimit='', $pbDebug=false)
    {
            $oSelect = new dal\Select($this);
            $oSelect->addField($pasFields);
            $oSelect->addTable($this->_table);
            $oSelect->where($psWhere);

            if (!empty($pasLimit) && is_array($pasLimit))
            {
                    $nStart = isset($pasLimit['start']) ? $pasLimit['start'] : 0;
                    $nNb 	= isset($pasLimit['nb']) ? $pasLimit['nb'] : 0;

                    if ($nNb > 0 || $nStart > 0)
                    {
                            $oSelect->limit($nStart, $nNb);
                    }
            }

            if ($pbDebug)
                $oSelect->_debug = true;

            if ($pbDebug)
            {
                utils\Debug::dump($oSelect->__toString());			
                die();
            }

        return $oSelect->fetchAll();
    }
    
    
    /**
        *  Generic method for selection row(s) depending of the where clause
        *
        * @param Array $pasFields
        * @param String $psWhere
        * @param Array $pasLimit
    */
    protected function _selectForDatatable($pasFields, $pasSearcheableFields, $pnStart=0, $pnLimit=0, $psSort='', $psSearch='', $poSelect='', $pasExcludeSearchFields=array(), $i=0)
    {
        if (! $poSelect instanceof dal\Select)
        {
            $oSelect = new dal\Select($this);
            $oSelect->addFields($pasFields);
            $oSelect->addTable($this->_table);
        }
        else
        {
            $oSelect = $poSelect;
        }
        
        if (!empty($psSearch))
        {
            
//            utils\Debug::e($pasSearcheableFields);
            
            foreach ($pasSearcheableFields as $sField)
            {
                if (!in_array($sField, $pasExcludeSearchFields))
                {
                    if ($i == 0 && !isset($pasSearcheableFields[1]) )
                        $oSelect->andWhere (''.$sField.' LIKE "%'.$psSearch.'%"');
                    elseif ($i == 0 )
                        $oSelect->andWhere ('('.$sField.' LIKE "%'.$psSearch.'%"');
                    elseif ($i > 0 && $i == (count($pasSearcheableFields) - 1 ))
                        $oSelect->orWhere ($sField.' LIKE "%'.$psSearch.'%" ) ');
                    else
                        $oSelect->orWhere ($sField.' LIKE "%'.$psSearch.'%"');
                }
                
                $i++;
            }
        }

        if ($pnLimit > 0)
        {
            $oSelect->limit($pnStart, $pnLimit);
        }

        if (!empty($psSort))
        {
            $oSelect->orderBy($psSort);
        }
        
        return $oSelect;
    }
    
    
    public function selectForDatatable($pasFields, $pasSearcheableFields, $pnStart=0, $pnLimit=0, $psSort='', $psSearch='', $poSelect='')
    {
        $oSelect = $this->_selectForDatatable($pasFields, $pasSearcheableFields, $pnStart, $pnLimit, $psSort, $psSearch, $poSelect);
        
        return $oSelect->fetchAll();
    }
    

    /**
        * Return a single record (P_Core_System_Dal_RowResponse Object)
        *
        * @param Array $pasFields
        * @param String $psWhere
        * @param Array $pasLimit
        * @param Boolean $pbDebug
        * @throws ErrorException
        * @return P_Core_System_Dal_RowResponse
        */
    public function selectOne($pasFields='', $psWhere='', $pasLimit='', $pbDebug=false)
    {
        $oDbResponse = $this->select($pasFields, $psWhere, $pasLimit, $pbDebug);

        while ($oRecord = $oDbResponse->readNext())
        {
            return $oRecord;
        }

        if ($this->haltNoResults)
            throw new \ErrorException( $oDbResponse->query.'<br />There is not any records in this query <br /><br />');
    }
    
    
    public function countByPK($pnPK)
    {
        $sPrimary = $this->getPrimary();
        
        return $this->count(array($sPrimary => $pnPK));
    }


    /**
        * Generic method to count row depending of the Where clause
        *
        * @param Array $pasWhere
        * @param String $psSeparator
        * @throws ErrorException
        */
    public function count($pasWhere=array(), $psSeparator='AND', $pbDebug=false)
    {
        if (!is_array($pasWhere)) throw new ErrorException('$pasWhere must be an array');

        $sWhere = '';
        if (!empty($pasWhere))
        {
            $asQuery = array();
            foreach ($pasWhere as $sField => $sValue)
            {
                $sOperator = '=';
                if (preg_match('/^([^a-z]+)(.*)/i', $sField, $asMatches))
                {

                    $sOperator = $asMatches[1];
                    $sField = $asMatches[2];
                }

                $asQuery[] = $sField.$sOperator.'"'.$sValue.'"';
            }

            $sWhere .= ' '.implode(' '.$psSeparator.' ', $asQuery);
        }


        $oRecord = $this->selectOne(array('COUNT(*) as count'), $sWhere, '');

        //Debug::dump($oRecord);

        if (isset($oRecord->count))
            return (int) $oRecord->count;

        throw new \ErrorException('$oRecord[0]->count is not set');
    }


    /**
        * Shortcut for execution Insert or Update on the current record
        *
        * @param Array $pasFields
        * @param Integer $pnPK
        */
    public function save($pasFields, $pnPK=0, $pbDebug=false)
    {
        if ($pnPK > 0)
            return $this->update($pasFields, (int) $pnPK, $pbDebug);
        else
            return $this->insert($pasFields, $pbDebug);
    }


    /**
        * Generic Method to insert a record in the current $_table
        *
        * @param Array $pasFields
        */
    public function insert($pasFields, $pbDebug = false)
    {
        $sQuery  = '';
        $sQuery .= ' INSERT INTO '.$this->_table;
        $sQuery .= ' SET ';

        // on gère la date automatique
        $sCreate = $this->_table.'_date_create';
        $sUpdate = $this->_table.'_date_update';

        if (isset($this->_scheme->$sCreate) && !isset($pasFields[$sCreate]))
        {
            $pasFields[$sCreate] = date('Y-m-d H:i:s');
        }

        if (isset($this->_scheme->$sUpdate) && !isset($pasFields[$sUpdate]))
        {
            $pasFields[$sUpdate] = date('Y-m-d H:i:s');
        }

        $asQuery = array();
        foreach ($pasFields as $sField => $sValue)
        {
            if (isset($this->_scheme->$sField))
            {
                $asQuery[] = $this->_renderValue($sField, $sValue, $pbDebug);
            }
        }

        $sQuery .= implode(', ', $asQuery);

        if ($pbDebug)
        {
            utils\Debug::e($sQuery);
            utils\Debug::log($sQuery);
        }
        
        $this->dal->query($sQuery) or die($sQuery.' <br /> '. $this->dal->errorCode().' --  '.utils\Debug::dump($this->dal->errorInfo()));

        return $this->dal->lastInsertId();
    }


    /**
     * Generic method to update a row of the current $_table
     *
     * @param Array $pasFields
     * @param Integer $pnId
     */
    public function update($pasFields, $pnPK, $pbDebug=false)
    {
        $sQuery  = '';
        $sQuery .= ' UPDATE '.$this->_table;
        $sQuery .= ' SET ';

        // on gère la date automatique
        $sUpdate = $this->_table.'_date_update';

        if (isset($this->_scheme->$sUpdate) && !isset($pasFields[$sUpdate]))
        {
            $pasFields[$sUpdate] = date('Y-m-d H:i:s');
        }

        $asQuery = array();
        foreach ($pasFields as $sField => $sValue)
        {
            if (isset($this->_scheme->$sField))
            {
                // foreign == forcement une cle numerique
                
                if ($this->_scheme->$sField->foreign && $this->_scheme->$sField->inputType != 'text')
                {
//                    utils\Debug::e($this->_scheme->$sField);
                    $asQuery[] = '`'.$sField.'`'.' = '.$sValue;
//                    die();
                }
                else
                    $asQuery[] = '`'.$sField.'`'.' = "'.  addslashes ($sValue).'"';
            }
            elseif ($pbDebug)
            {
                throw new \ErrorException('Le champ '.$sField.' est inconnu ! ');
            }
        }
        
//        utils\Debug::e($pasFields);
//        utils\Debug::e($asQuery);
        

        $sQuery .= implode(', ', $asQuery);

        $sQuery .= ' WHERE '.$this->getPrimary().' = '.(int) $pnPK;

        if ($pbDebug)
        {
            utils\Debug::e($pasFields);
            utils\Debug::e($sQuery);
            //die();
        }

        $this->dal->query($sQuery) or die($sQuery.' <br /> '.  utils\Debug::dump($this->dal->errorInfo()));;

        return (int) $pnPK;
    }


    /**
        * Generic method for deleting a row by its Primary Key value
        *
        * @param Integer $pnPK
        */
    public function delete($pnPK, $pbDebug=false)
    {
        $sQuery = ' DELETE FROM `'.$this->_table.'` WHERE '.$this->getPrimary().' = '.(int) $pnPK;

        if ($pbDebug)
        {
            utils\Debug::dump($sQuery);
            die();
        }

        $this->dal->query($sQuery) or die($sQuery.'<br />'.utils\Debug::dump(mysql_error()));

        return !(bool) $this->count(array($this->getPrimary() => $pnPK));
    }
    

    /**
        * Performs the query and return the generic oDbResponse Object needed by the Dal
        */
    public function query($psQuery)
    {
        $oResults = $this->dal->query($psQuery) or die($psQuery.'<br />'.utils\Debug::e($this->dal->errorInfo()));

        if (is_object($oResults))
        {
            $oDbResponse = new system\dal\DbResponse($oResults->fetchAll(\PDO::FETCH_OBJ), $this);

            return $oDbResponse;
        }
    }


    /**
        *  Reset the values of the scheme loaded in
        */
    public function reset()
    {
        foreach ($this->_scheme as $oField)
        {
            $oField->value = '';
        }
    }


    /**
        * handle the rendering of the item according to its type
        *
        * @param String $psField
        * @param String $psValue
        * @throws ErrorException
        */
    protected function _renderValue($psField, $psValue, $pbDebug=false)
    {
        $sType = 'unknown';
        if (isset($this->_scheme->$psField) && is_object($this->_scheme->$psField))
            $sType = $this->_scheme->$psField->getType();
        else
        {
            utils\Debug::dump($this->_scheme->$psField);
            throw new \ErrorException($psField.' n existe pas (valeur : '.$psValue.') - table '.$this->_table);
        }

//        if ($pbDebug)
//            utils\Debug::e('Type de '.$psField.' : '.$sType);
        
        switch ($sType)
        {
            case 'varchar':
            case 'text':
                return '`'.$psField.'`'.' = "'.addslashes($psValue).'"';
                break;

            case 'date':
            case 'datetime':
                return '`'.$psField.'`'.' = "'.utils\Date::toDatabase($psValue).'"';
                break;

            default:
                return '`'.$psField.'`'.' = "'.addslashes($psValue).'"';
                break;
        }
    }
    
    
    public function toForm($pnPK=0)
    {
        $oForm = new form\Form($this);
        
        if ($pnPK > 0)
            $this->load($pnPK);
        
        $this->populateForm($oForm);
        
        // we add a mention to the curent referer
        if (isset($_SERVER["HTTP_REFERER"]) && !empty( $_SERVER["HTTP_REFERER"]))
         {
             $oField = new dal\schema\Field('referer');
             $oField->value =  $_SERVER["HTTP_REFERER"];
             $oField->setType('hidden');
             
             $oFormField = form\FormBuilder::buildField($oField, $oForm);
             
             $oForm->addField($oFormField);
         }
        
        return $oForm;
    }
    
    
    public function populateForm(form\Form $poForm)
    {
        $_scheme = $this->getScheme();
        
        foreach ($_scheme as $oField)
        {
            if ($oField->writable)
            {
                $oFormField = form\FormBuilder::buildField($oField, $poForm);

                $poForm->addField($oFormField);
            }
        }
    }
    
    
    public function saveForm(form\Form $poForm, $pnPK=0, $pbDebug=false)
    {
        $asSave = array();
        
        $aoFields = $poForm->getFields(true);   
        
        foreach ($aoFields as $oFormField)
        {
            if (!$oFormField instanceof form\elements\Fieldset)
            {
                if ($oFormField->getDataField()->recordable)
                {
                    $sData = addslashes($oFormField->getDataField()->value);

                    if ($oFormField instanceof form\elements\Date)
                    {
                        $sData = utils\Date::toDatabase($oFormField->getDataField()->value);
                    }
                    // anticipation of any foreign key constrait fail
                    if (!$oFormField->getDataField()->foreign || !empty($sData))
                    {
                        $asSave[$oFormField->getDataField()->getName()] = $sData;
                    }
                    else
                    {
                        $asSave[$oFormField->getDataField()->getName()] = 'NULL';
                    }
                }
            }
            else
            {
                foreach ($oFormField->_fields as $oFormField)
                {
                    if ($oFormField->getDataField()->recordable)
                    {
                        $sData = mysql_real_escape_string($oFormField->getDataField()->value);
                       
                        if ($oFormField instanceof form\elements\Date)
                        {
                            $sData = utils\Date::toDatabase($oFormField->getDataField()->value);
                        }

                        if (!$oFormField->getDataField()->foreign || !empty($sData))
                        {
                            $asSave[$oFormField->getDataField()->getName()] = $sData;
                        }
                        else
                        {
                            $asSave[$oFormField->getDataField()->getName()] = 'NULL';
                        }
                    }
                }
            }
        }

        return $this->save($asSave, $pnPK, $pbDebug);
    }
    
    
    
    public function getLabelFieldName()
    {
        return 'name';
    }
    
    
     public function toArray($aoResults, $psFieldName='')
    {
        if (!is_object($aoResults))
        {
            return array();
        }
        
        $aoResults->reset();
        
        $anId = array();
        
        while($oRecord = $aoResults->readNext(RESPONSE_RAW))
        {
            $sPrimary = $this->getPrimary();

//            utils\Debug::e($oRecord->$sPrimary);
            
            if (!empty($psFieldName))
            {
                $anId[] = $oRecord->$psFieldName;
            }
            else 
            {
                $asRow = array();
                
                foreach ($oRecord as $sName => $sValue)
                {
                    $asRow[$sName] = $sValue;
                }
                
                $anId[$oRecord->$sPrimary] = $asRow;
            }
        }
        
        return $anId;
    }
    
    
    public function exist($id)
    {
        return (bool) $this->count(array($this->getPrimary() => $id));
    }
    
    
    public function getFieldValue($psField, $poSelect, $default=false)
    {
        if ($poSelect instanceof dal\Select)
        {
//            $poSelect->resetFields();
//            $poSelect->addField($psField);
            $oRecord = $poSelect->fetchOne();
        }
        else
            $oRecord = $poSelect;
        
        if (isset($oRecord->$psField))
            return $oRecord->$psField;
        
        if ($poSelect instanceof dal\Select)
            $sQuery = $poSelect->__toString();
        else
        {
            $sQuery = '';
            if (isset($poSelect->query))
                $sQuery = $poSelect->query;
        }
        
//        \P\lib\framework\helpers\Message::setMessage('Le champ '.$psField.' n\'existe pas dans la requete '.$sQuery);
//        
//        throw new \ErrorException();
        return $default;
    }
}