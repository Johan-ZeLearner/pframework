<?php

namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\utils\Debug as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class Color extends Text
{
	protected $_type;

	public function getField()
	{
		helpers\JSManager::addFile('colorpicker.js', 'resources/js/colorPicker/');
		helpers\CSSManager::addFile('colorpicker.css', 'resources/css/colorPicker/');
		
		$sDefaultColor = '#0000ff';
		
		if (!empty($this->_params['value']))
			$sDefaultColor = '#'.$this->_params['value'];
		
		$sJS = '
		
			$("#'.$this->getId().'").ColorPicker({
				color: "'.$sDefaultColor.'",
				onShow: function (colpkr) {
					$(colpkr).fadeIn(500);
					return false;
				},
				onHide: function (colpkr) {
					$(colpkr).fadeOut(500);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					$("#'.$this->getId().' div").css("backgroundColor", "#" + hex);
					$("#'.$this->getId().'_input").val(hex);
				}
			});
		
		';
		
		$this->addJS($sJS);
		helpers\JSManager::addInstructions($sJS);
		
		$sHTML = \P\tag('div',
                                        \P\tag('div', '', array('style' => 'background-color: '.$sDefaultColor)),
					array('id' => $this->getId(), 'class' => 'colorSelector'));
					
		$sHTML .= \P\tag('input', '', array('type' => 'hidden', 'name' => $this->getId(), 'id' => $this->getId().'_input', 'value' => $sDefaultColor));
		
		return $sHTML;
	}
}