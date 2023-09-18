<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/** @property string $zdess_show_reg_date */

class UserOption extends XFCP_UserOption
{
    protected function _setupDefaults()
    {
        parent::_setupDefaults();

        $options = \XF::options();

        $defaults = $options->registrationDefaults;
        $this->zdess_show_reg_date = isset($defaults['zdess_show_reg_date']) ?? (bool) $defaults['zdess_show_reg_date'];
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_show_reg_date'] = ['type' => self::BOOL, 'default' => false];

        return $structure;
    }
}