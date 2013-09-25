<?php
namespace P\lib\framework\core\system\dal;
use P\lib\framework\core\system as system;
use P\lib\framework\core\utils as utils;

/**
 * Manage the creation and the caching of the various databaseScheme class to be used by the framework
 *
 * @author johan
 * @version 0.1
 */
class SchemeCreator
{
	private $_table;
	private $_bRequireConstant = true;
	
	
	/**
	 * initialize the generation of the content of the constant file and the members file
	 * then it launch the file creation
	 *
	 *	$_scheme is the object representation of the fields of the table
	 *	$psSchemeClassName is the name of the class to generate
	 *	$psTable is the name of the table
	 *
	 * @param Object $_scheme
	 * @param String $psSchemeClassName
	 * @param String $psTable
	 */
	public function __construct($_scheme, $pasInfos, $psSchemeClassName, $psTable)
	{
		$this->_table = $psTable;
		
		// on crée les membres
		$sBody 		= $this->_createBody($_scheme, $pasInfos);
		$sOutput 	= $this->_createOutput($sBody, $psSchemeClassName);

                // on crée le fichier
		$this->_createMemberFile($sOutput, $psSchemeClassName);
		
		// On s'occupe du fichier de constantes
		$sConstants = $this->_createConstants($_scheme);
		
		$this->_createConstantsFile($sConstants, $psSchemeClassName);
		
		$this->_bRequireConstant = false;
		// On Fait la même chose pour les instructions des champs
		$sInstructions = $this->_createInstructions($_scheme);
		
		$this->_createInstructionsFile($sInstructions, $psSchemeClassName);
	}
	
	
	/**
	 * Build the constructor for the class to be generated
	 *
	 * @param Object $_scheme
	 */
	private function _createBody($_scheme, $pasInfos)
	{
		$sBody = '';
		
		$sBody .= "\t".'public function __construct()'."\n";
		$sBody .= "\t".'{'."\n";
		$sBody .= "\t\t".'$this->_infos = '.$this->_toString($pasInfos).';'."\n";
		$sBody .= "\t\t".'$this->_scheme = new \StdClass();'."\n\n";
		
		foreach ($_scheme as $oField)
		{
                    // prevent from deleted database field
                    if ($oField instanceof schema\Field)
                    {
                        $sBody .= $this->_createFieldOutput($oField);
                    }
		}
		
		$sBody .= "\t".'}'."\n";
			
		$sBody .= "\n\n\t".'public function getScheme()'."\n";
		$sBody .= "\t".'{'."\n";
		$sBody .= "\t\t".'return clone $this->_scheme;'."\n";
		$sBody .= "\t".'}'."\n\n";
		
		return $sBody;
	}
	
	
	/**
	 * Create the php code for the field $poField
	 * It escapes the necessary values and returns the php output.
	 *
	 * @param Field $_scheme
	 * @return String PHP code
	 */
	private function _createFieldOutput($poField)
	{
            
		$sOutput = '';
		$sOutput .= "\t\t".'$oField = new \P\lib\framework\core\system\dal\schema\Field(\''.$poField->getName().'\');'."\n";
		$sOutput .= "\t\t".'$oField ->setSqlType(\''.$poField->getSqlType().'\');'."\n";
		$sOutput .= "\t\t".'$oField ->setType(\''.$poField->getType().'\');'."\n";
	
		$nLength = (int) $poField->getLength();
                
		if ($nLength > 0)
			$sOutput .= "\t\t".'$oField ->setLength(\''.$poField->getLength().'\');'."\n";
		
		$bKey = $poField->isKey();
		
		if ($bKey)
			$sOutput .= "\t\t".'$oField ->setKey(\'MUL\');'."\n";
			
		$sOutput .= "\t\t".'$oField ->label	  		= '.strtoupper('LABEL_'.$this->_table.'_'.$poField->getName()).';'."\n";
		$sOutput .= "\t\t".'$oField ->description	= '.strtoupper('INSTRUCTION_'.$this->_table.'_'.$poField->getName()).';'."\n";
		$sOutput .= "\t\t".'$oField ->readable  	= '.$this->_toString($poField->readable).';'."\n";
		$sOutput .= "\t\t".'$oField ->writable  	= '.$this->_toString($poField->writable).';'."\n";
		$sOutput .= "\t\t".'$oField ->recordable  	= '.$this->_toString($poField->recordable).';'."\n";
		$sOutput .= "\t\t".'$oField ->browsable  	= '.$this->_toString($poField->browsable).';'."\n";
		$sOutput .= "\t\t".'$oField ->pattern   	= '.$this->_toString(addslashes($poField->pattern)).';'."\n";
		$sOutput .= "\t\t".'$oField ->value 		= '.$this->_toString($poField->value).';'."\n";
		$sOutput .= "\t\t".'$oField ->primary 		= '.$this->_toString($poField->primary).';'."\n";
		$sOutput .= "\t\t".'$oField ->foreign 		= '.$this->_toString($poField->foreign).';'."\n\n";
		
		
		
		$sOutput .= "\t\t".'$oField ->setForeignTable(\''.$poField->getForeignTable().'\');'."\n";
		$sOutput .= "\t\t".'$oField ->setForeignField(\''.$poField->getForeignField().'\');'."\n";
		$sOutput .= "\t\t".'$oField ->setForeignLabelField(\''.$poField->getForeignLabelField().'\');'."\n\n";
		
		$sOutput .= "\t\t".'$oField ->options   	= '.$this->_toString($poField->options).';'."\n";
		
		
		
		$sOutput .= "\t\t".'$this->_scheme->'.$poField->getName().' = $oField;'."\n\n";
		
		return $sOutput;
	}
	
	
	/**
	 * Create the layout of the file - the php tag and the structure of the class.
	 * It return the whole class to write in a single file.
	 *
	 * @param String $psBody
	 * @param String $psSchemaClassName
	 * @return String PHP code
	 */
	private function _createOutput($psBody, $psSchemeClassName)
	{
            $asItem = explode('\\', $psSchemeClassName);
            
            $sClassName = ucfirst($asItem[count($asItem) - 1]);
            $sNamespace = system\PathFinder::removeTrailingFile($psSchemeClassName, '\\');
                
            
		$sOutput = '';
		
		$sOutput .= '<?php'."\n";
		$sOutput .= "\n";
		$sOutput .= 'namespace '.$sNamespace.';'."\n";
		//$sOutput .= "require 'constant/".strtolower($sClassName).".php';";
		//$sOutput .= "require 'instructions/".strtolower($sClassName).".php';";
		$sOutput .= "\n";
		$sOutput .= "\n";
		$sOutput .= 'class '.$sClassName."\n";
		$sOutput .= '{'."\n";
		$sOutput .= "\t".'public $_scheme;'."\n\n";
		$sOutput .= "\t".'public $_infos;'."\n\n";
		$sOutput .= $psBody;
		$sOutput .= '}'."\n";
		
		return $sOutput;
	}
	
	
	/**
	 * Create the php file with the generated content $psOutput
	 *
	 * @param String $psSchemaClassName
	 * @param String $psOutput
	 */
	private function _createMemberFile($psOutput, $psSchemeClassName)
	{
		// on genere le schema d'après la table et on crée le fichier
		$sClassPath = system\PathFinder::classToPath($psSchemeClassName);

		// on trouve le repertoire hebergeant les schemas
		$sSchemeDir = system\PathFinder::getFileDir($sClassPath);
		
		// on crée le repertoire s'il n'existe pas
		if (!is_dir($sSchemeDir))
			mkdir($sSchemeDir);
			
		file_put_contents($sClassPath, $psOutput);
		
		//chmod($sClassPath, 0777);
	}
	
	
	/**
	 * Create the constant file with the generated content
	 *
	 * @param String $psOutput
	 * @param String $psSchemeClassName
	 */
	private function _createConstantsFile($psOutput, $psSchemeClassName)
	{
		// on trouve le repertoire hebergeant les schemas
		$sConstDir = system\PathFinder::getConstDir($psSchemeClassName);
		
		// on crée le repertoire s'il n'existe pas
		if (!is_dir($sConstDir))
			mkdir($sConstDir);
			
		$sFileName = $sConstDir.$this->_table.'.php';
					
		file_put_contents($sFileName, $psOutput);
		
		//chmod($sFileName, 0777);
		
		if ($this->_bRequireConstant)
			require $sFileName;
	}
	
	
	/**
	 * Create the constant file with the generated content
	 *
	 * @param String $psOutput
	 * @param String $psSchemeClassName
	 */
	private function _createInstructionsFile($psOutput, $psSchemeClassName)
	{
		// on trouve le repertoire hebergeant les schemas
		$sInstructionsDir = system\PathFinder::getInstructionsDir($psSchemeClassName);
		
		// on crée le repertoire s'il n'existe pas
		if (!is_dir($sInstructionsDir))
			mkdir($sInstructionsDir);
			
		$sFileName = $sInstructionsDir.$this->_table.'.php';
					
		file_put_contents($sFileName, $psOutput);
		
		//chmod($sFileName, 0777);
		
		if ($this->_bRequireConstant)
			require $sFileName;
	}
	
	
	/**
	 * Helper to format the correct output submited.
	 *
	 * @param Mixed $pData
	 */
	private function _toString($pData)
	{
		if (is_bool($pData))
		{
			if ($pData)  return 'true';
			else return 'false';
		}
		elseif (is_array($pData))
		{
			$sOutput = '';
			
			$sOutput .= 'array('."\n";
			$asTemp = array();
			foreach ($pData as $sKey => $sValue)
                        {
                            if (is_array($sValue) && $sKey == '_class')
                            {
                                
                                $sValue = implode(', ', $sValue);
                                
                            }
                            
                            $asTemp[] = "\t\t\t\t\t\t\t\t".'"'.$sKey.'" => "'.$sValue.'"'."\n";
                            
			}
			
			$sOutput .= implode(', ', $asTemp);
			
			$sOutput .= "\t\t\t\t\t\t\t".')';
			
			return $sOutput;
		}
		elseif (is_integer($pData))
		{
			return $pData;
		}
		else
			return '"'.$pData.'"';
	}
	
	
	/**
	 * Generate the content of the constant file.
	 * It takes care of the existing values of the constants if they already exixt.
	 *
	 * @param Object $_scheme
	 */
	private function _createConstants($_scheme)
	{
            $sOutput = '<?php '."\n\n";

            $i=0;
            foreach ($_scheme as $oField)
            {
                if ($oField instanceof schema\Field)
                {
                    $sConstant = strtoupper('LABEL_'.$this->_table.'_'.$oField->getName());
                    
                    if ($i == 0)
                    {
                        $sOutput .= 'if (!defined(\''.$sConstant.'\'))'."\n";
                        $sOutput .= '{'."\n";
                    }
                    
                    $i++;
                    
                    $sValue = $oField->getName();
                    if (defined($sConstant))
                    {
                        $sValue = addslashes(constant($sConstant));
                        $this->_bRequireConstant = false;
                    }


                    $sOutput .= 'define(\''.$sConstant.'\',\''.$sValue.'\');'."\n";
                    
                }
            }
            
             $sOutput .= '}'."\n";

            return $sOutput;
	}
	
	
	/**
	 * Generate the content of the field instructions file.
	 * It takes care of the existing values of the constants if they already exixt.
	 *
	 * @param Object $_scheme
	 */
	private function _createInstructions($_scheme)
	{
		$sOutput = '<?php '."\n\n";
		
                $i=0;
		foreach ($_scheme as $oField)
		{
                    if ($oField instanceof schema\Field)
                    {
                        $sConstant = strtoupper('INSTRUCTION_'.$this->_table.'_'.$oField->getName());
                        
                        if ($i == 0)
                        {
                            $sOutput .= 'if (!defined(\''.$sConstant.'\'))'."\n";
                            $sOutput .= '{'."\n";
                        }
                        
                         $sOutput .= $this->_getConstantValue($sConstant, $oField->getName());
                        
                       
                        $i++;
                    }
		}
                
                $sOutput .= '}'."\n";
		
		$sOutput .= '//------------ Constantes de titres ---------------//'."\n";
		
                $sOutput .= 'if (!defined(strtoupper(\'INDEX_'.$this->_table.'\')))'."\n";
                $sOutput .= '{'."\n";
		$sOutput .= $this->_getConstantValue('INDEX_'.$this->_table, 	'Liste des '.$this->_table.'s');
		$sOutput .= $this->_getConstantValue('ADD_'.$this->_table, 		'Ajouter un '.$this->_table);
		$sOutput .= $this->_getConstantValue('EDIT_'.$this->_table, 	'Modifier un '.$this->_table);
		$sOutput .= $this->_getConstantValue('DELETE_'.$this->_table, 	'Supprimer un '.$this->_table);
		
		$sOutput .= $this->_getConstantValue('ADD_OK_'.$this->_table, 		$this->_table.' créé avec succès');
		$sOutput .= $this->_getConstantValue('ADD_KO_'.$this->_table, 		$this->_table.' n\'a pas pu être créé');
		$sOutput .= $this->_getConstantValue('EDIT_OK_'.$this->_table, 		$this->_table.' modifié avec succès');
		$sOutput .= $this->_getConstantValue('EDIT_KO_'.$this->_table, 		$this->_table.' n\'a pas pu être modifié');
		$sOutput .= $this->_getConstantValue('DELETE_OK_'.$this->_table, 	$this->_table.' supprimé avec succès');
		$sOutput .= $this->_getConstantValue('DELETE_KO_'.$this->_table, 	$this->_table.'n\'a pas pu être supprimé.');
                $sOutput .= '}'."\n";
		
		return $sOutput;
	}
	
	
	/**
	 * Return the declaration of the costant $psName
	 *
	 * @param String $psName
	 * @param String $psDefault
	 */
	private function _getConstantValue($psName, $psDefault)
	{
	    $psName = strtoupper($psName);
            $sValue =  addslashes($psDefault);
            if (defined($psName))
            {
                $sValue = addslashes(constant($psName));
                $this->_bRequireConstant = false;
            }

            return 'define(\''.strtoupper($psName).'\',\''.$sValue.'\');'."\n";
	}
	
	
	/**
	 * Get the Database scheme and map it to the DB Object
	 *
	 * @param String $psClassName
	 * @param String $psTable
	 * @param Object $poDal
	 */
	public static function createDbScheme($psClassName, $psTable, $poDal)
	{
            self::createDbSchemeFromTable($psClassName, $psTable, $poDal);

            // on surcharge ces paramètres depuis les données en base
            //self::updateDbSchemeFromDatabase($psClassName, $psTable, $poDal);
	}
	
	
	/**
	 * Static Method to build the $_scheme object used to map Database Field to the Scheme object to be created
	 *
	 * @param String $psClassName
	 * @param String $psTable
	 * @param P_Core_Abstract_Dal $poDal
	 */
	public static function createDbSchemeFromTable($psClassName, $psTable, $poModel)
	{
		$oTable = new schema\Table($psTable, $poModel->dal);
		
		$asScheme = $oTable->getTableInfo();
                
		$asInfos = array();
                
                
                if (!is_array($asScheme))
                    utils\Debug::e($asScheme);
		

                foreach ($asScheme as  $asRow)
		{
                    $sFieldName = $asRow['Field'];

                    $oField = new schema\Field($sFieldName);

                    $oField->setSqlType($asRow['Type']);

                    
                    if (preg_match('/^(enum)(.*)/i', $asRow['Type']))
                    {
                        $oField->setSqlType('varchar');
                    }
                    
                    
                    $oField->setType($oField->getSqlType());
                    
                    /**
                     * Configuration des constantes (formulaire & listings)
                     */
                    if (defined(strtoupper('LABEL_'.$psTable.'_'.$sFieldName)))
                            $oField->label = constant(strtoupper('LABEL_'.$psTable.'_'.$sFieldName));

                    if (defined(strtoupper('INSTRUCTION_'.$psTable.'_'.$sFieldName)))
                            $oField->description = constant(strtoupper('INSTRUCTION_'.$psTable.'_'.$sFieldName));

                    $oField		->setKey($asRow['Key']);

                    if ($asRow['Key'] == 'PRI')
                    {
                        $oField->primary = true;
                        $asInfos['primary'] = $sFieldName;
                        $oField->setType('hidden');
                    }
                    
                    // on gère les champs date_create/update
                    if (preg_match('/date_(create|update)/', $sFieldName))
                    {
                        $oField->setType('hidden');
                    }
                    
                    // on recherche les foreign keys
                    if ($asRow['Key'] == 'MUL')
                    {
                        $oField->foreign = true;
                        $oField->setType('autocomplete');
                        $asResults = $oTable->getForeignInformation($sFieldName, $psTable);
                        if (isset($asResults['CONSTRAINT_SCHEMA']))
                        {
                            $oField->setForeignTable($asResults['REFERENCED_TABLE_NAME']);
                            $oField->setForeignField($asResults['REFERENCED_COLUMN_NAME']);
                            $oField->setForeignLabelField($asResults['label_field']);
                        }
                    }
                    
                    $poModel->_scheme->$sFieldName = $oField;
		}
		
		// on surcharge les données avant la génération
		$poModel->customFields();
                
		// On crée le fichier
		new SchemeCreator($poModel->_scheme, $asInfos, $psClassName, $psTable);
	}
	
	
	/**
	 * Get the database scheme stored in database and overwrite the existing scheme properties
	 *
	 * @param String $psClassName
	 * @param String $psTable
	 * @param P_Core_Abstract_Dal $poModel
	 */
	public static function updateDbSchemeFromDatabase($psClassName, $psTable, P_Core_Abstract_Dal $poModel)
	{
	    // on evite la recursion si p_controller ou p_table_field font appelle au scheme creator.
	    if ($psTable == 'p_controller' || $psTable == 'p_table_field')
	        return false;
	    
		$oController = ClassManager::getInstance('P_Src_App_Controller_Controller');
                $oTableField = ClassManager::getInstance('P_Src_App_TableField_TableField');

                $asInfos = array();
        
		if ($oController->exists($psTable))
		{
		    $nControllerPK = $oController->getPk($psTable);
		    
                    $oDbResponse = $oTableField->selectByController($nControllerPK);

                    //Debug::dump($oDbResponse);

                    $bEmpty = true;
                    while ($oRecord = $oDbResponse->readNext())
                    {
                        $bEmpty = false;

                        $sFieldName = $oRecord->p_table_field_name;

                        if (isset($poModel->_scheme->$sFieldName) && $poModel->_scheme->$sFieldName instanceof P_Core_System_Dal_Field)
                        {
                                // on update
                                $oField = self::updateSchemeField($poModel->_scheme->$sFieldName, $oRecord, $psTable, $sFieldName);
                        }
                        else
                        {
                                // on supprime
                                $oTableField->deleteByPK($oRecord->p_table_fieldpk);
                        }
                    }

                    if ($bEmpty) return false;


                    // on surcharge les données avant la génération
                    $poModel->customFields();

                    // On crée le fichier
                    new SchemeCreator($poModel->_scheme, $asInfos, $psClassName, $psTable);

                    return true;
		}
	}
	
	
	/**
	 *
	 * @param Object $poField
	 * @param Object $poRecord
	 * @param String $psTable
	 * @param String $psFieldName
	 * @throws ErrorException
	 */
	public static function updateSchemeField($poField, $poRecord, $psTable, $psFieldName)
	{
		if (preg_match('/^(enum)(.*)/i', $poRecord->p_table_field_type))
		{
		    $poField ->setSqlType('varchar');
		}
		else
		{
		    $poField ->setSqlType($poRecord->p_table_field_type);
		}
                
                $poField->setType($oField->getSqlType());
		
		/**
		 * Configuration des constantes (formulaire & listings)
		 */
		if (defined(strtoupper('LABEL_'.$psTable.'_'.$psFieldName)))
			$poField->label = constant(strtoupper('LABEL_'.$psTable.'_'.$psFieldName));
		
		if (defined(strtoupper('INSTRUCTION_'.$psTable.'_'.$psFieldName)))
			$poField->description = constant(strtoupper('INSTRUCTION_'.$psTable.'_'.$psFieldName));
		
	
		if ($poRecord->p_table_field_key == 'PRI')
		{
		    $poField->primary = true;
                    $poField->setType('hidden');
                    $asInfos['primary'] = $psFieldName;
		}
		
		if ($poRecord->p_table_field_key == 'MUL')
		{
		    $poField->foreign = true;
		    
		    $poField->setForeignField($poRecord->p_table_field_foreign_field);
		    $poField->setForeignTable($poRecord->p_table_field_foreign_table);
		    $poField->setForeignLabelField($poRecord->p_table_field_foreign_field_label);
                    
                    // par defaut on passe le type à autocomplete
                    $poField->setType('autocomplete');
		}
		
		$poDal->_scheme->$psFieldName = $poField;
		
		if (! $poDal->_scheme->$psFieldName instanceof schema\Field)
		    throw new \ErrorException('Probleme de champ - n\'est pas instance de Field');
	}
	
}
