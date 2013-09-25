<?php
namespace P\lib\framework\core\system\dal;
use P\lib\framework\core\system as system;
/**
 *
 * This class extends PDO and aim to handle the connection to the database
 * @author johan
 *
 */
class Dal extends \PDO
{
    public $driver;
    public $schema;
    public $host;
    public $password;
    public $username;
    
	/**
	 * This method use the static class Settings ans read the config/config.ini file with the database information
	 * Example of ini file :
	 * 
	 * [database]
	 * driver = mysql
	 * host = localhost
	 * ;port = 3306
	 * schema = database_name
	 * username = yourusername
	 * password = yourpassword
	 * 
	 */
    public function __construct($psName, $pasArgs=array())
    {
        if (!empty($pasArgs))
        {
            $this->driver       = $pasArgs['driver'];
            $this->host         = $pasArgs['host'];
            $this->schema       = $pasArgs['schema'];
            $this->password     = $pasArgs['password'];
            $this->username     = $pasArgs['username'];
        }
        else
        {
            $this->driver 	= system\Settings::getParam($psName, 'driver');
            $this->host         = system\Settings::getParam($psName,'host');
            $this->schema 	= system\Settings::getParam($psName, 'schema');
            $this->password     = system\Settings::getParam($psName, 'password');
            $this->username     = system\Settings::getParam($psName, 'username');
        }
        
        $dns 		= $this->driver.':host='.$this->host.';dbname='.$this->schema;
       
        try {
            parent::__construct($dns, $this->username, $this->password);
        }        
        catch (\ErrorException $e)
        {
            echo $e->getMessage();
        }
        
        return $this;
    }
    

    /**
     * Returns the name of cached schema structure of the ClassName / TableName submitted
     * 
     * @param String $psCalledClass
     * @param String $psTableName
     */
    public function getSchemeClassName($psCalledClass, $psTableName)
    {
        return system\PathFinder::removeTrailingFile($psCalledClass, '\\').'\\'.ucfirst($psTableName);
    }
    
    
    public function getConnexionInfos()
    {
        $asConnexion = array(
            'driver'    => $this->driver,
            'host'      => $this->host,
            'schema'    => $this->schema,
            'name'      => $this->schema,
            'username'  => $this->username,
            'password'  => $this->password
        );
        
        return $asConnexion;
    }
}
