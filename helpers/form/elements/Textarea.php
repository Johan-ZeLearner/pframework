<?php

namespace P\lib\framework\helpers\form\elements;

class Textarea extends Element
{
	protected $_type = 'textarea';
	
	public function getField()
	{
            if ($this->_errorStatus)
                $this->addClass('error');

            $this->_params['type'] = $this->_type;

            $sValue = '';
            if (isset($this->_params['value']))
            {
                $sValue = $this->_params['value'];
                unset($this->_params['value']);
            }

            return \P\tag('textarea', $sValue, $this->_params);
	}
}