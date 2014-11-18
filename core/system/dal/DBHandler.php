<?php
namespace P\lib\framework\core\system\dal;

class DBHandler
{
    static $handler;
    public $aoDB = array();
    
    
    public function __construct()
    {
        self::$handler = $this;
        return $this;
    }
    
    
    /**
     * Create a new Dal instance with $psConfig Settings entry or $pasArgs params.
     * 
     * Return the connexion index name for self::getDB()
     * 
     * @param type $psConfigSection
     * @param type $pasArgs
     * @return type
     */
    public function connect($psConfigSection, $pasArgs=array())
    {
        if (!empty($pasArgs))
        {
            $this->aoDB[$psConfigSection] = new Dal($psConfigSection, $pasArgs);
            $sIndex = $psConfigSection;
        }
        else
        {
            $this->aoDB[] = new Dal($psConfigSection);
            $sIndex = (count($this->aoDB) - 1);
        }
        
        $this->aoDB[$sIndex]->query('SET CHARACTER SET utf8');
        
        return $psConfigSection;
    }
    
   
    public function connectInformationSchema($psConfigSection)
    {
        $this->aoDB['information_schema'] = new Dal($psConfigSection);
    }
   
    
    public function connectPhpMyAdmin($psConfigSection)
    {
        $this->aoDB['phpmyadmin'] = new Dal($psConfigSection);
    }
    
    
    public static function getDB($psDatabase=0, $pasArgs=array())
    {
        if (isset(self::$handler->aoDB[$psDatabase]))
            return self::$handler->aoDB[$psDatabase];
        elseif(!empty($pasArgs))
        {
            self::$handler->connect($psDatabase, $pasArgs);
            return self::$handler->aoDB[$psDatabase];
        }
        
        return false;
    }


   /**
    * Retourne les infos de SHOW FIELDS sur la table courante
    *
    */
    public static function getDatabaseInfo($pnDatabaseNumber=0)
    {
        $sQuery = 'SHOW TABLES ';
        
        $oDb    = self::getDB($pnDatabaseNumber);
        
        $oSth   = $oDb->prepare($sQuery);
        $oSth   ->execute();
        
        return $oSth->fetchall();//->fetchAll();// or die($sQuery.' '.$oDb->errorCode().' - '.$oDb->errorInfo());
    }
    
    
    public static function connectMysqli($pasData=array())
    {
        if (empty($pasData))
        {
            $sHost              = 'localhost';
            $sSchema            = system\Settings::getParam('database', 'schema');
            $sPassword          = system\Settings::getParam('database', 'password');
            $sUsername          = system\Settings::getParam('database', 'username');
        }
        else
        {
            $sHost              = $pasData['host'];
            $sSchema            = $pasData['schema'];
            $sPassword          = $pasData['password'];
            $sUsername          = $pasData['username'];
        }
        
        $oDb = new \mysqli ($sHost, $sUsername, $sPassword, $sSchema);
        
        if($oDb->connect_errno > 0)
        {
            throw new \ErrorException('Unable to connect to database [' . $oDb->connect_error . ']');
        }
        
        return $oDb;
    }
}
?>