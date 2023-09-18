<?php

namespace ZD\IS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property string $zdis_username_css_class
 */
class UserGroup extends XFCP_UserGroup
{
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isChanged('zdis_username_css_class'))
        {
            $this->rebuildDisplayStyleCache();
        }
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdis_username_css_class'] = ['type' => self::STR, 'maxLength' => 75, 'default' => ''];

        return $structure;
    }
}