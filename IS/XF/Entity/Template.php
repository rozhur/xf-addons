<?php 

namespace ZD\IS\XF\Entity;

class Template extends XFCP_Template
{
    protected function _preSave()
    {
        parent::_preSave();

        $this->_errors = array_filter($this->_errors, function ($error)
        {
            return !($error instanceof \XF\Phrase) || $error->getName() !== 'admin_templates_may_only_be_created_in_master_style';
        });
    }
}
