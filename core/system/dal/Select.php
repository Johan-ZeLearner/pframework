<?php
namespace P\lib\framework\core\system\dal;

use P\lib\framework\core\utils as utils;

class Select implements \P\lib\framework\core\system\interfaces\isDbCollection
{
    protected $_model;
    protected $_asFields          = array();
    protected $_asWhere           = array();
    protected $_asOrderBy         = array();
    protected $_asLimit           = array();
    protected $_asTables          = array();
    protected $_asJoin            = array();
    protected $_sGroupBy          = '';
    protected $_customInstruction = '';
    public    $_debug             = false;
    protected $_count             = false;
    protected $_prefix            = '';
    protected $_prefixEnabled     = true;

    protected $_sWhere;
    protected $_sHaving;
    protected $_oResults;
    protected $_engine;

    public $count  = false;
    public $render = true;

    public $showFields = false;

    public $dal;

    public $response;

    const ENGINE_MYSQL_PDO = 'engine_mysql_pdo';
    const ENGINE_MYSQLI    = 'engine_mysqli';


    /**
     * Registers the DAL to be used
     *
     * @param Object $poDal
     */
    public function __construct($poModel = null, $psEngine = self::ENGINE_MYSQL_PDO, $pasConnexionData = array())
    {
        $this->_model  = $poModel;
        $this->_engine = $psEngine;

        if ($this->_engine == self::ENGINE_MYSQL_PDO)
        {
            if (is_object($this->_model))
            {
                $this->dal = $this->_model->dal;
            }
            else
            {
                $this->dal = DBHandler::$handler->getDB();
            }
        }
        else
        {
            if (empty($pasConnexionData))
            {
//                    throw new \ErrorException("Can't connect to MySqli without connexion infos");

//                utils\Debug::e($this->_model);
//                die();

                $this->dal = DBHandler::connectMysqli($this->_model->dal->getConnexionInfos());
            }
            else
            {
                $this->dal = DBHandler::connectMysqli($pasConnexionData);
            }
        }

        return $this;
    }


    public function resetFields()
    {
        $this->_asFields = array();
    }


    /**
     * By default the table of the Dal is used.
     * One or more tables can be specified
     *
     * @param String $psTable
     * @param String $psAlias
     */
    public function addTable($psTable, $psAlias = '')
    {
        $this->_asTables[$psTable] = $psAlias;

        if (empty($this->_prefix) && !empty($psAlias) && $psAlias != $psTable)
        {
            $this->_prefix = $psAlias;
        }

        return $this;
    }


    /**
     * By default the fields of the Dal are used.
     * One or more fields can be specified
     *
     * @param unknown_type $psField
     */
    public function addField($psField)
    {
        if (is_array($psField))
        {
            return $this->addFields($psField);
        }

        if (empty($psField))
        {
            $psField = ' * ';
        }

        if ($this->_prefixEnabled && !empty($this->_prefix))
        {
            $this->_asFields[] = $this->_prefix . '.' . $psField;
        }
        else
        {
            $this->_asFields[] = $psField;
        }

        return $this;
    }


    /**
     * Same as addField but with an associative array
     *
     * @param Array $pasField
     *
     * @throws ErrorException
     */
    public function addFields($pasField)
    {
        if (!is_array($pasField))
        {
            throw new \ErrorException('$pasField must be an array');
        }
        foreach ($pasField as $sField)
        {
            if (!empty($sField))
            {
                $this->addField($sField);
            }
        }

        return $this;
    }


    public function leftJoin($psTable, $psJoin)
    {
        $this->_asJoin[] = ' LEFT JOIN `' . $psTable . '` ON ' . $psJoin;
    }


    /**
     * Set a where clause. By default it is a "OR WHERE" clause
     *
     * @param String $psWhere
     */
    public function where($psWhere)
    {
        return $this->orWhere($psWhere);
    }


    /**
     * Set a or where clause
     *
     * @param String $psWhere
     */
    public function orWhere($psWhere)
    {
        $asWhere = array(
            'query'     => $psWhere,
            'separator' => 'OR'
        );

        $this->_asWhere[] = $asWhere;

        return $this;
    }


    /**
     * Set a and whetr clause
     *
     * @param String $psWhere
     */
    public function andWhere($psWhere)
    {
        $asWhere = array(
            'query'     => $psWhere,
            'separator' => 'AND'
        );

        $this->_asWhere[] = $asWhere;

        return $this;
    }


    public function having($string)
    {
        $this->_sHaving = $string;

        return $this;
    }


    public function remove($query)
    {
        foreach ($this->_asWhere as $key => $where)
        {
            if (preg_match('/' . $query . '/i', $where['query']))
            {
                unset ($this->_asWhere[$key]);
            }
        }
    }


    /**
     * Set the ordre by clause
     *
     * @param unknown_type $psOrderBy
     */
    public function orderBy($psOrderBy)
    {
        $this->_asOrderBy[] = $psOrderBy;

        return $this;
    }


    /**
     * Set the group By Clause
     *
     * @param unknown_type $psField
     */
    public function groupBy($psField)
    {
        $this->_sGroupBy = $psField;
    }


    /**
     * Set the limit clause
     *
     * @param Integer $pnStart
     * @param Integer $pnLength
     *
     * @throws ErrorException
     */
    public function limit($pnStart, $pnLength)
    {
        if (empty($pnLength) || $pnLength < 0)
        {
            throw new \ErrorException('$pnLength must be > 0');
        }
        if ($pnStart < 0)
        {
            throw new \ErrorException('$pnStart must be >= 0');
        }

        $this->_asLimit['start']  = $pnStart;
        $this->_asLimit['length'] = $pnLength;

        return $this;
    }


    /**
     * Set manually written SQL instructions
     *
     * @param String $psInstruction
     */
    public function setCustomInstruction($psInstruction)
    {
        $this->_customInstruction = $psInstruction;

        return $this;
    }


    public function showFields()
    {
        $this->showFields = true;
    }


    public function toSQL()
    {
        return (string)$this;
    }


    /**
     * Render the query
     */
    public function __toString()
    {
        $sQuery = '  ';
//            $sQuery .= ' SET CHARACTER SET utf8; ';

        if ($this->showFields)
        {
            $sQuery .= ' SHOW FIELDS ';
        }
        else
        {
            $sQuery .= ' SELECT ' . $this->_customInstruction . ' ';
            $sQuery .= $this->_getFields();
        }

        $sQuery .= ' FROM ' . $this->_getTables(true) . '';

        if (!empty($this->_asJoin))
        {
            foreach ($this->_asJoin as $sJoin)
            {
                $sQuery .= $sJoin;
            }
        }

        if (!empty($this->_asWhere))
        {
            $this->_getWhere();
            if (!empty($this->_sWhere))
            {
                $sQuery .= ' WHERE ' . $this->_sWhere;
            }
        }


        if (!empty($this->_sGroupBy))
        {
            $sQuery .= ' GROUP BY ' . $this->_sGroupBy;
        }


        if (!empty($this->_sHaving))
        {
            $sQuery .= ' HAVING ' . $this->_sHaving;
        }


        if (!empty($this->_asOrderBy))
        {
            $sQuery .= ' ORDER BY ' . $this->_getOrderBy();
        }


        if (!empty($this->_asLimit))
        {
            $sQuery .= ' LIMIT ' . $this->_getLimit();
        }


        return $sQuery;
    }


    /**
     *
     * Execute the query
     */
    public function query()
    {
        if ($this->count)
        {
            $this->setCustomInstruction('SQL_CALC_FOUND_ROWS');
        }

        $sQuery = $this->__toString();

        if ($this->_debug)
        {
            utils\Debug::dump($sQuery);
        }

        if ($this->_engine == self::ENGINE_MYSQL_PDO)
        {
            $this->_oResults = $this->dal->query($sQuery) or $this->_debugTrace();
        }
        elseif ($this->_engine == self::ENGINE_MYSQLI)
        {
            $this->_oResults = $this->dal->query($sQuery);

//                utils\Debug::e($this->_oResults);

//                die();

        }
    }


    /**
     * Fetch all the results and returns a DbResponse Object
     */
    public function fetchAll()
    {
        if (!$this->_oResults)
        {
            try
            {
                $this->query();
            }
            catch (\ErrorException $e)
            {
                throw new \ErrorException($this->toSQL());
                utils\Debug::e($e->getMessage());
                utils\Debug::e($this->toSQL());
                die();
            }
        }

        if ($this->_engine == self::ENGINE_MYSQL_PDO)
        {
            $oDbResponse = new DbResponse($this->_oResults->fetchAll(\PDO::FETCH_OBJ), $this->_model);
        }
        elseif ($this->_engine == self::ENGINE_MYSQLI)
        {
            $oDbResponse = new DbResponse($this->_oResults->fetch_object(), $this->_model);
        }

        $oDbResponse->query  = $this->__toString();
        $oDbResponse->count  = $this->getCount();
        $oDbResponse->render = $this->render;

        return $oDbResponse;
    }


    public function fetchOne()
    {
        if (empty($this->_asLimit))
        {
            $this->limit(0, 1);
        }
        $this->render = false;
        $oDbResponse  = $this->fetchAll();

//            if (key_exists('ps_carrier', $this->_asTables))
//                utils\Debug::e($oDbResponse);


        while ($oRecord = $oDbResponse->readNext())
        {
            return $oRecord;
        }
    }


    /**
     * Returns the number of rows of the record
     *
     * @param Boolean $pbForce
     */
    public function getCount($pbForce = false)
    {
        if (is_integer($this->_count))
        {
            return $this->_count;
        }

        if (!$this->count || $pbForce)
        {
            $this->count     = true;
            $this->_oResults = false;
        }

        if (!$this->_oResults)
        {
            $this->query();
        }

        if ($this->_engine == self::ENGINE_MYSQLI)
        {
            $this->_count = $this->_oResults->num_rows;

            return $this->_count;
        }

        $sQuery = 'SELECT FOUND_ROWS() as count';

        $oResults = $this->dal->query($sQuery) or $this->_debugTrace();

        if ($this->_engine == self::ENGINE_MYSQL_PDO)
        {
            $oDbResponse = new DbResponse($oResults->fetchAll(\PDO::FETCH_OBJ), $this->_model);

            while ($oRecord = $oDbResponse->readNext())
            {
                $this->_count = $oRecord->count;

                return (int)$this->_count;
            }
        }
    }


    /**
     * Disable the prefix of teh tables
     */
    public function disablePrefix()
    {
        $this->_prefixEnabled = false;
    }


    /**
     * Return the fields list
     */
    protected function _getFields()
    {
        if (empty($this->_asFields))
        {
            return ' * ';
        }

        return implode(', ', $this->_asFields);
    }


    /**
     * Return the table(s) list
     */
    protected function _getTables()
    {
        $asTables = array();

        foreach ($this->_asTables as $sTable => $sAlias)
        {
            if (!empty($sAlias) && $this->_prefixEnabled)
            {
                if ($sTable == 'order')
                {
                    $asTables[] = '`' . $sTable . '` ' . $sAlias;
                }
                else
                {
                    $asTables[] = $sTable . ' ' . $sAlias;
                }
            }
            else
            {
                if ($sTable == 'order')
                {
                    $asExpr = explode(' ', $sTable);

                    if (count($asExpr) > 1)
                    {
                        $asTables[] = '`' . $asExpr[0] . '` ' . $asExpr[1];
                    }
                    else
                    {
                        $asTables[] = '`' . $sTable . '`';
                    }
                }
                else
                {
                    $asData = explode(' ', $sTable);

                    $sString = '`' . $asData[0] . '`';

                    if (isset($asData[1]))
                    {
                        $sString .= ' ' . $asData[1];
                    }

                    $asTables[] = $sString;
//                        $asTables[] = $sTable;
                }
            }
        }

        $sTable = implode(', ', $asTables);

        if (!empty($sTable) && !empty($this->_asJoin))
        {
            $sTable = '(' . $sTable . ')';
        }


        if (empty($sTable) && is_object($this->_model))
        {
            $sTable = $this->_model->getTable();
        }


        return $sTable;
    }


    /**
     * Returns the content of the WHERE clause
     */
    protected function _getWhere()
    {
        if (!empty($this->_sWhere))
        {
            return $this->_sWhere;
        }

        foreach ($this->_asWhere as $i => $asWhere)
        {
            $sSeparator = '';
            if ($i > 0)
            {
                $sSeparator = ' ' . trim($asWhere['separator']) . ' ';
            }

            $this->_sWhere .= $sSeparator . $asWhere['query'];
        }

        return $this->_sWhere;
    }


    /**
     * Returns the content of the ORDER BY clause
     */
    protected function _getOrderBy()
    {
        return implode(', ', $this->_asOrderBy);
    }


    /**
     * Returns the content of the LIMIT clause
     */
    protected function _getLimit()
    {
        return $this->_asLimit['start'] . ', ' . $this->_asLimit['length'];
    }


    /**
     * Display the debugging
     */
    protected function _debugTrace()
    {
        echo $this->__toString() . '<br />' . utils\Debug::e($this->dal->errorInfo());
        echo '<br />' . utils\Debug::e($this->dal->errorCode());
        throw new \ErrorException('Echec de la requete');
        die();
    }


    public function render($pbRender = false)
    {
        $this->render = $pbRender;
    }


//    public function resetFields()
//    {
//        $this->_asFields = array();
//    }


    public function reset()
    {
        if (!is_object($this->response))
        {
            $this->response = $this->fetchAll();
        }

        return $this->response->reset();
    }


    public function next()
    {
        $this->render = false;

        return $this->readNext();
    }


    public function readNext()
    {
        if (!is_object($this->response))
        {
            $this->response = $this->fetchAll();
        }

        return $this->response->next();
    }


    public function hasNext()
    {
        if (!is_object($this->response))
        {
            $this->response = $this->fetchAll();
        }

        return $this->response->hasNext();
    }


    public function toArray()
    {
        if (!is_object($this->response))
        {
            $this->response = $this->fetchAll();
        }

        return $this->response->toArray();
    }
}
