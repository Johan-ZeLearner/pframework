<?php
namespace P\lib\framework\helpers;
use P\lib\framework\core\system\abstractClasses as abs;
use P\lib\framework\core\utils as utils;
/**
 * This class generate raw HTML table output
 *
 * Example 1 : one kind of array submitted to addLine():
 * array(
 * 		'line' => array(
 * 			'_id' => 'id_line',
 * 			'header' => true
 * 		),
 * 		0 => array(
 * 			'data' 	=> 'Foo',
 * 			'_id'	=> 'id_foo',
 * 			'_class'=> 'class_foo'
 * 		),
 * 		1 => array(
 * 			'data' 	=> 'Bar',
 * 			'_id'	=> 'id_bar',
 * 			'_class'=> 'class_bar'
 * 		)
 * )
 *
 * it will we be rendered as :
 *  <thead>
 * 	<tr id="id_line">
 *          <th id="id_foo" class="class_foo">FOO</th>
 *          <th id="id_bar" class="class_bar">BAR</th>
 *  	</tr>
 *  </thead>
 *
 *  =================================================== *
 *  Example 2 : another kind of array submited to addLine():
 * array(
 * 		'line' => array(
 * 			'_id' => 'id_line'
 * 		),
 * 		0 => array(
 * 			'data' 	=> 'Foo',
 * 			'_id'	=> 'id_foo',
 * 			'_class'=> 'class_foo',
 * 			'header'=> true
 * 		),
 * 		1 => array(
 * 			'data' 	=> 'Bar',
 * 			'_id'	=> 'id_bar',
 * 			'_class'=> 'class_bar'
 * 		)
 * )
 *
 * it will we rendered as :
 *  <tr id="id_line">
 * 	<th id="id_foo" class="class_foo">FOO</th>
 * 	<td id="id_bar" class="class_bar">BAR</td>
 *  </tr>
 * 
 *  =================================================== *
 *  Example 3 : basic array submited to addLine():
 *  array('Foo', 'Bar', 'FooFoo', 'BarBar', 'FooBar')
 *
 * it will we rendered as :
 *  <tr>
 * 	<td>Foo</th>
 * 	<td>Bar</td>
 * 	<td>FooFoo</th>
 * 	<td>BarBar</td>
 * 	<td>FooBar</th>
 *  </tr>
 * 
 * @author Johan
 * @version 0.1
 *
 */
class Table
{
	protected $_params;
	protected $_lineHeader;
	protected $_lineBody;
	protected $_lineFooter;
	protected $_maxCols;
	protected $_currentCol;
	
	protected $_asDataHeader;
	protected $_asDataBody;
	protected $_asDataFooter;
	protected $_oDbResponse;
	protected $_oBaseActionUrl;
	protected $_anLineId 	= array();
	protected $_asCustomCol = array();
	
	protected $_asSorting;
		
	
	/**
	 * The constructor accespts two types of arguments :
	 * - an array of params
	 * - a P_Core_System_Abstract_Dal Instance
	 *
	 * By default, it returns an empty array
	 *
	 * @param Mixed $pmArgs
	 */
	public function __construct($pmArgs=array())
	{
		$this->_lineHeader 	= 0;
		$this->_lineBody 	= 0;
		$this->_lineFooter 	= 0;
		$this->_maxCols 	= 0;
		$this->_currentCol	= 0;
		
		if (is_array($pmArgs))
			$this->_params 		= $pmArgs;
		elseif ($pmArgs instanceof abs\Model)
			$this->_buildFromDal($pmArgs);

		// We set the Cellspacing and Cellpadding to 0
		if (!isset($pmArgs['_cellpadding']))
			$this->_params['_cellpadding'] = 0;

		if (!isset($pmArgs['_cellspacing']))
			$this->_params['_cellspacing'] = 0;
			
		$this->_params['baseUrl'] = new utils\Url();
	}
	
	
	/**
	 * Set the title for the <caption> tag of the table
	 *
	 * @param String $psTitle
	 */
	public function setTitle($psTitle)
	{
		if (empty($psTitle)) throw new \ErrorException('$psTitle must not be empty');
		
		$this->setParam('title', $psTitle);
	}
	
	
	/**
	 * Shortcut to set the id of the <table> tag
	 *
	 * @param String $psId
	 */
	public function setId($psId)
	{
		if (empty($psId)) throw new \ErrorException('$psId must not be empty');
		
		$this->setParam('_id', $psId);
	}
	
	
	/**
	 * Shortcut to set the class of the <table> tag
	 *
	 * @param String $psClass
	 */
	public function setClass($psClass)
	{
		if (empty($psClass)) throw new \ErrorException('$psClass must not be empty');
		
		$this->setParam('_class', $psClass);
	}
	
	
	/**
	 * GEneric method for setting parametters to the <table> tag
	 * It will throw an ErrorException if the parameter name is empty
	 *
	 * @param String $psParamName
	 * @param String $psValue
	 * @throws ErrorException
	 */
	public function setParam($psParamName, $psValue)
	{
		if (empty($psParamName)) throw new \ErrorException('$psParamName must not be empty');
		
		$this->_params[$psParamName] = $psValue;
	}
	
	
	/**
	 * Add a custom Column to the table layout
	 *
	 * @param Array of String $pasCol
	 */
	public function setCustomCol($pasCol)
	{
		$this->_asCustomCol[] = $pasCol;
	}
	
	
	/**
	 * Add a line to the table by passing an multi dimensionnal associative array
         * or a basic ordered list array
	 *
	 * It will throws an ErrorException if the parameter is not an array
	 *
	 * @param Array $pasLine
	 * @throws ErrorException
	 */
	public function addLine($pasLine)
	{
		if (!array($pasLine)) throw new \ErrorException('$pasLine must be an array');
		
		if (isset($pasLine['line']['header']) && (bool) $pasLine['line']['header'])
		{
			$this->_asDataHeader[] = $pasLine;
			$this->_lineHeader++;
			$this->_checkMaxCols($pasLine);
		}
		elseif (isset($pasLine['line']['footer']) && (bool) $pasLine['line']['footer'])
		{
			$this->_asDataFooter[] = $pasLine;
			
			$this->_lineHeader++;
		}
		else
		{
			$this->_asDataBody[] = $pasLine;
			$this->_lineBody++;
		}
	}
	
	
	/**
	 * Add a column at the line $_line of the table
	 * by passing an associative array
	 *
	 * It will throw an ErrorException if the parameter is not an array
	 *
	 * @param Array $pasCol
	 * @throws ErrorException
	 */
	public function addColArray($pasCol)
	{
		if (!array($pasCol)) throw new \ErrorException('$pasCol must be an array');
		
		$this->_asDataBody[$this->_lineBody][] = $pasCol;
		$this->_currentCol++;
		
	}
	
	
	/**
	 * Add a column to the $_lineBody line
	 *
	 * @param String $psCol
	 * @param Boolean $pbHeader
	 */
	public function addCol($psCol='&nbsp;', $pbHeader=false)
	{
		$this->addColArray(array('data' => $psCol, 'header' => (bool)$pbHeader));
	}
	
	
	/**
	 * Check $_maxCols and iterate to the next line
	 * Is used only if $this->addcol() is used
	 *
	 */
	public function newLine()
	{
		$this->_lineBody++;
		
		// on vérifie _maxCols et on reset _currentCols
		$this->_maxCols = max(array($this->_currentCol, $this->_maxCols));
		$this->_currentCol = 0;
	}
	
	
	/**
	 * Render the table as a HTML String
	 *
	 * @return String
	 */
	public function getTable()
	{
		$sHeader 	= $this->_renderTableSection($this->_asDataHeader, true);
		$sBody 		= $this->_renderTableSection($this->_asDataBody);
		$sFooter 	= $this->_renderTableSection($this->_asDataFooter);
		
		$sOutput        = '';
		
		if (!empty($sHeader))
		{
			$sOutput .= '<thead>';
			$sOutput .= $sHeader;
			$sOutput .= '</thead>';
		}
		
		if (!empty($sBody))
		{
			if (!empty($sHeader))
				$sOutput .= '<tbody>';
			
			$sOutput .= $sBody;

			if (!empty($sHeader))
				$sOutput .= '</tbody>';
		}
			
		if (!empty($sFooter))
		{
			$sOutput .= '<tfoot>';
			$sOutput .= $sFooter;
			$sOutput .= '</tfoot>';
		}
		
		
		$sTable = '';
		$sTable .= '<table'.$this->_getParamsString($this->_params).'>';
	//	$sTable .= 	'<caption></caption>';
		$sTable .=	$sOutput;
		$sTable .= '</table>';
		
		
		return $sTable;
	}
	
	
	/**
	 * PHP Internal Shortcut to $this->getTable()
	 *
	 * @return String
	 */
	public function __toString()
	{
		return $this->getTable();
	}
	
	
	/**
	 * Enable sorting to the array (not implemented yet)
	 *
	 * @param Array $pasSorting
	 */
	public function setSorting($pasSorting)
	{
		$this->_asSorting = $pasSorting;
	}
	
	
	/**
	 * Count the number of cols in the line $pasLine
	 * and update the status of $_maxCols if necessary
	 *
	 * @param Array $pasLine
	 */
	protected function _checkMaxCols($pasLine)
	{
		$nCount = count($pasLine);
		
		if (isset($pasLine['header']) && (bool) $pasLine['header']) $nCount--;
		if (isset($pasLine['footer']) && (bool) $pasLine['footer']) $nCount--;
		
		$this->_maxCols = max(array($nCount, $this->_maxCols));
	}
	
	
	/**
	 * Render the parameters as a String of HTML parameters
	 *
	 * @param Array $pasParams
	 */
	protected function _getParamsString($pasParams)
	{
		if (empty($pasParams) || !is_array($pasParams)) return '';
		
		$sOutput = '';
		foreach ($pasParams as $sKey => $sValue)
		{
			//Debug::dump($sKey);
			if ((bool) preg_match('/^([_]+)/', $sKey))
				$sOutput .= ' '.substr($sKey, 1).'="'.$sValue.'"';
		}
		
		return $sOutput;
	}
	
	
	/**
	 * Render the section submited as a HTML String

	 * @param Array $pasSection
	 */
	protected function _renderTableSection($pasSection, $pbHeader=false)
	{
		if (empty($pasSection) || !is_array($pasSection)) return '';
		
		$sOutput = '';
				
		foreach ($pasSection as $nLineNumber => $asLine)
		{
		    $sId = '';
		    if (!$pbHeader && isset($this->_anLineId[$nLineNumber]))
		        $sId = ' id="'.$this->_anLineId[$nLineNumber].'"';
		    
			$sOutput .= '<tr'.$sId.'>'."\n";
			$nbCol = 0;
			foreach ($asLine as $nColNumer => $asData)
			{
				if (is_integer($nColNumer))
				{
					$sTag 		= 'td';
					$sContent 	= '&nbsp;';
					
					if (isset($asData['header']) && $asData['header'] ) 	$sTag = 'th';
					if (isset($asData['data'])) 	$sContent = $asData['data'];
					
					$sOutput .= '<'.$sTag.$this->_getParamsString($asData).'>'.$sContent.'</'.$sTag.'>'."\n";
					
					$nbCol++;
					
				}
			}
			
			while ($nbCol < ($this->_maxCols - 1))
			{
				$sOutput .= '<td>&nbsp;</td>'."\n";
				$nbCol++;
			}
			
			$sOutput .= '</tr>'."\n";
		}
		
		return $sOutput;
	}
	
	
	/**
	 * Load the Data directly from the DAL $poDal
	 * You can specify if only headers are rendered of both header and table data
	 *
	 * @param P_Core_Abstract_Dal $poDal
	 * @param Boolean $pbHeaderOnly
	 */
	public function load(P_Core_Abstract_Dal $poDal, $pbHeaderOnly=false)
	{
		$this->_buildFromDal($poDal, $pbHeaderOnly);
	}
	
	
	
	/**
	 * Set a custom Select object request
	 *
	 * @param P_Core_System_Dal_DbResponse $poDbResponse
	 */
	public function setDbResponse( $poDbResponse)
	{
        $this->_oDbResponse = $poDbResponse;
	}
	
	
	/**
	 * Return a generic Object Response or a custom one if it exists
	 *
	 * @param P_Core_System_Abstract_Dal $poDal
	 */
	protected function _getDbResponse($poDal)
	{
	    if (empty($this->_oDbResponse))
	        $this->_oDbResponse = $poDal->select($poDal->getFieldNames('browsable'), '', '', false);
	    
	    return $this->_oDbResponse;
	}
	
	
	/**
	 * Internal method for crawling the DAL data
	 *
	 * @param P_Core_Abstract_Dal $poDal
	 * @param Boolean $pbHeaderOnly
	 */
	protected function _buildFromDal(P_Core_Abstract_Dal $poDal, $pbHeaderOnly=false)
	{
        $asFieldNames   = $poDal->getFieldLabels('browsable');

        $asHead = array();
        $i      = 0;
        foreach ($asFieldNames as $sName)
        {
            $asHead[$i]['data'] = $sName;
            $asHead[$i]['header'] = true;
            $i++;
        }


        if (isset($this->_params['edit']) && $this->_params['edit'] == true)
        {
            $asHead[$i]['data'] = '&nbsp;';
            $asHead[$i]['header'] = true;
            $i++;
        }


        if (isset($this->_params['delete']) && $this->_params['delete'] == true)
        {
            $asHead[$i]['data'] = '&nbsp;';
            $asHead[$i]['header'] = true;
            $i++;
        }

        if (!empty($this->_asCustomCol))
        {
            $nNb = count($this->_asCustomCol);

            for($j=0; $j<$nNb; $j++)
            {
                $asHead[$i]['data'] = '&nbsp;';
                $asHead[$i]['header'] = true;
                $i++;
            }
        }

        $asHead['line']['header'] = true;

        $this->addLine($asHead);

        if (!$pbHeaderOnly)
        {
            $oDbResponse = $this->_getDbResponse($poDal);

            while ($oRecord = $oDbResponse->readNext())
            {
                $sFieldPK = $poDal->getPrimary();
                $nPK = $oRecord->$sFieldPK;

                $sPrimary = $poDal->getPrimary();

                foreach ($oRecord as $sName => $sValue)
                {
                    if (!preg_match('/^raw_/', $sName))
                        $this->addCol(tag('a', $sValue, array('href' => \P\url(true)->setParam('action', 'zoom')->setParam('key', $oRecord->$sPrimary))));
                }


                if (!empty($this->_asCustomCol))
                {
                        foreach ($this->_asCustomCol as $asCol)
                        {
                                $oUrl = url();
                                if (isset($asCol['controller']))
                                        $oUrl->setParam(CONTROLLER, $asCol['controller']);

                                if (isset($asCol['action']))
                                        $oUrl->setParam(ACTION, $asCol['action']);

                                if (isset($asCol['key']) && $asCol['key'] == true)
                                        $oUrl->setParam('key', $nPK);

                                $this->addCol(tag('a', $asCol['label'], array('href' => $oUrl)));
                        }
                }


                if (isset($this->_params['edit']) && $this->_params['edit'] == true)
                {
                    $oUrl = $this->getBaseActionUrl();

                    $oUrl->setParams(array('action' => 'edit', 'key' => $nPK));

                        $this->addCol(tag('a', tag('img', '', array('src' => HOST_URL.'/resources/css/img/led-ico/pencil.png')), array('href' => $oUrl)));
                }


                if (isset($this->_params['delete']) && $this->_params['delete'] == true)
                {
                    $oUrl = $this->getBaseActionUrl();

                    $oUrl->setParams(array('action' => 'delete', 'key' => $nPK));
                        $this->addCol(tag('a', tag('img', '', array('src' => HOST_URL.'resources/css/img/led-ico/delete.png')), array('href' => $oUrl)));
                }


                $this->newLine();
            }
        }
    }


    /**
        *
        * Set a link on the line of the table
        *
        * @param P_Core_Utils_Url $poUrl
        */
    public function lineAddLink($poUrl)
    {
        if (!isset($this->_anLineId[$this->_lineBody]))
            $this->_anLineId[$this->_lineBody] = uniqid('table');

        $sJS = '
            jQuery("#'.$this->_anLineId[$this->_lineBody].'").mouseover(function() { jQuery(this).css("cursor", "pointer"); jQuery(this).css("color", "red");});
            jQuery("#'.$this->_anLineId[$this->_lineBody].'").mouseout(function() { jQuery(this).css("color", "#00305d"); });
            jQuery("#'.$this->_anLineId[$this->_lineBody].'").click(function() { location.href = "'.$poUrl.'"; });
        ';

        JSManager::addInstructions($sJS);
    }


    /**
        * Set a custom baseUrl for further action linking

        * @param P_Core_Utils_Url $poUrl
        */
    public function setBaseActionUrl(P_Core_Utils_Url $poUrl)
    {
        $this->_oBaseActionUrl = $poUrl;
    }


    /**
        * Get the Base Url
        */
    public function getBaseActionUrl()
    {
        if (empty($this->_oBaseActionUrl)) return \P\url();

        return clone $this->_oBaseActionUrl;
    }
}