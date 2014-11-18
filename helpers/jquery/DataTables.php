<?php
namespace P\lib\framework\helpers\jquery;
use P\lib\framework\helpers as helpers;
/**
 *
 * This class embed the support of the datatables jQuery Plugin for rendering pagination skilled Tables of Data
 * It extends the functionnalities of P_Core_Helpers_Table
 *
 * @author johan
 * @see P_Core_Helpers_Table
 */
class DataTables extends helpers\Table
{
	public 	$_source;
	private $_dom = '<"length"l><"search"f>rt<"pagination"ip><"clear">';
	
	
	/**
	 *
	 * The constructor set the jquery librairies on and
	 * communicate the argumenst to its parent constructor
	 *
	 * @param Mixed $pmArgs
	 */
	public function __construct($pmArgs=array())
	{
		//helpers\JSManager::jQueryEnable(true);
		//JSManager::addFile('jquery.dataTables.min.js');
		helpers\JSManager::addFile('jquery.dataTables.js');
		parent::__construct($pmArgs);
		
		$this->setClass('dataTables');
	}
	
	
	/**
	 *
	 * Set the Url of teh ajax source of the table.
	 * It also activate remote data loading and serverside data processing
	 *
	 * @param String $psUrl
	 */
	public function setAjaxSource($psUrl)
	{
		$this->_source = $psUrl;
	}
	
	
	/**
	 * @see P_Core_Helpers_Table::getTable()
	 */
	public function getTable()
	{
		if (!isset($this->_params['_id']) || empty($this->_params['_id'])) $this->setId('id_'.uniqid());
		
		if (!empty($this->_asCustomCol)) $this->_source .= '&customcol='.urlencode(serialize($this->_asCustomCol));
		
		$sJS = '
					
			jQuery(document).ready(function() {
				oTable = jQuery("#'.$this->_params['_id'].'").dataTable({
					"aaSorting":['.$this->_getSorting().'],
					"bStateSave": true,
					"iCookieDuration ": 120,
					"sPaginationType": "full_numbers",
					"sDom": \''.$this->_dom.'\',
					"oLanguage" : {
						"sProcessing":   "Traitement en cours...",
						"sLengthMenu":   "Afficher _MENU_ entrées",
						"sZeroRecords":  "Aucun élément à afficher",
						"sInfo":         "Affichage de l\'élement _START_ à _END_ sur _TOTAL_ ",
						"sInfoEmpty":    "Affichage de l\'élement 0 à 0 sur 0",
						"sInfoFiltered": "(filtré parmi _MAX_ entrées)",
						"sInfoPostFix":  "",
						"sSearch":       "Recherche ",
						"sUrl":          "",
						"oPaginate": {
							"sFirst":    "Premier",
							"sPrevious": "Précédent",
							"sNext":     "Suivant",
							"sLast":     "Dernier"
						}
					}
					';
		
					if (!empty($this->_source))
					{
						$sJS .=
					',
					"bProcessing": true,
					"bServerSide": true,
					"sAjaxSource": "'.$this->_source.'"';
					}
					
					$sJS .= '
				});
			} );
		';
		
		helpers\JSManager::addInstructions($sJS);
		
		return \P\tag('div', parent::getTable(), array('class' => 'datatables_container'));
	}
	
	
	/**
	 *
	 * Internal method for handling the sorting of the Table,
	 * if the parameter is set
	 */
	private function _getSorting()
	{
		if (empty($this->_asSorting)) return ' ';
		
		return json_encode($this->_asSorting);
	}
	
	
	/**
	 * Disable the search box
	 */
	public function disableSearch()
	{
		$this->_dom = '<"length"l>rt<"pagination"ip><"clear">';
	}
}
