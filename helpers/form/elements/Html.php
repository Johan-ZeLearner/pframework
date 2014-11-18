<?php

namespace P\lib\framework\helpers\form\elements;

class Html extends Element
{
    protected $_content;

    public function setContent($psContent)
    {
        $this->_content = $psContent;
    }


    public function getField()
    {
        //Debug::dump($this->_field);
        if (isset($this->_field->options['content']))
            $this->setContent($this->_field->options['content']);

        return \P\tag('div', $this->_content, $this->_params)."\n";
    }
}