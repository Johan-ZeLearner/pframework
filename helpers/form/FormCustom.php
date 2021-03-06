<?php
namespace P\lib\framework\helpers\form;

use P\lib\framework\core\system as system;
use P\lib\framework\core\utils as utils;
use P\lib\framework\core\system\traits as traits;

class FormCustom extends \P\lib\framework\helpers\form\Form
{
    public $redirectUrl;
    public $model;
    public $saved    = true;
    public $data     = array();
    public $messages = array();
    public $status   = -1;
    public $key      = 0;
    public $recordSaved;


    public function parentConstruct()
    {
        return parent::__construct();
    }


    public function __construct($poModel = '', $pbPopulate = true)
    {
        parent::__construct($poModel, $pbPopulate);

        $this->getModel();

        if ($this->setData())
        {
            $this->_fill();
        }
        else
        {
            $this->populateFromRawData();
        }

        if (utils\Http::isPosted() && $this->isSent())
        {
            if ($this->checkForm())
            {
                $this->saved = $this->saveForm();
            }
            elseif (!$this->isAjax() && !utils\Http::isAjax())
            {
//                utils\Debug::e('echech ckeck');
                \P\lib\framework\helpers\Message::setMessage($this->getMessages(), MESSAGE_ERROR);

                $this->saveRawData();
                utils\Http::redirect($this->getRedirectUrl()->setParam('form_error', 1));
            }
            else
            {
                \P\lib\framework\helpers\Message::instantMessage($this->getMessages(), MESSAGE_ERROR);
                $this->saved = false;
                $this->saveRawData();

                return false;
            }
        }

        if (!$this->saved)
        {
            $this->saveRawData();
            if (!$this->isAjax() && !utils\Http::isAjax())
            {
                utils\Http::redirect($this->getRedirectUrl()->setParam('form_error', 1));
            }

            return false;
        }
    }


    public function isSaveSuccess()
    {
        return $this->saved && utils\Http::isPosted() && $this->isSent();
    }


    public function isSent()
    {
        return true;
    }


    public function getModel()
    {

    }


    public function saveRawData()
    {

        $asData = array();
        foreach ($this->_fields as $oField)
        {
//            utils\Debug::e($oField->_field);

            $sFieldName          = $oField->_field->getName();
            $asData[$sFieldName] = $oField->_field->value;
        }

        $sData = serialize($asData);

        $sHash = md5(get_class());

        system\Session::set($sHash, $sData);

//        $sSession = system\Session::get($sHash);
    }


    public function populateFromRawData()
    {
        if (!utils\Http::isPosted())
        {
            $sData  = system\Session::get(md5(get_class()));
            $asData = unserialize($sData);

            foreach ($this->_fields as $oField)
            {
                if (isset($asData[$oField->_field->getName()]))
                {
                    $oField->setValue($asData[$oField->_field->getName()]);
                }
            }
        }

        system\Session::set(md5(get_class()), null);
    }


    public function checkForm()
    {
        throw new \ErrorException(__METHOD__ . ' must be implemented by ' . __CLASS__);
    }


    public function setData()
    {
        return false;
    }


    protected function _fill()
    {
        foreach ($this->_fields as $oField)
        {
            if (isset($this->data[$oField->_field->getName()]))
            {
                $oField->setValue($this->data[$oField->_field->getName()]);
            }
        }
    }


    public function reFill()
    {
        $this->setData();
        $this->_fill();
    }


    public function saveForm()
    {
        throw new \ErrorException(__METHOD__ . ' must be implemented by ' . __CLASS__);
    }


    public function __get($name)
    {
        return $this->getField($name);
    }


    public function getRedirectUrl()
    {
        if (empty($this->redirectUrl))
        {
            $this->redirectUrl = \P\url();
        }

        $this->redirectUrl->removeParam('form_error');

        return $this->redirectUrl;
    }


    public function setMessage($message)
    {
        $this->messages[] = $message;
    }


    public function getMessages()
    {
        return implode(';', $this->messages);
    }


    public function setAjax()
    {
        $this->isAjax();
    }


    public function getRecordSaved()
    {
        return $this->recordSaved;
    }


    public function get($field)
    {
        return (string)$this->getField($field)->__toString();
    }
}