<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * @property $zdess_super_admins_only
 */
class AdminNavigation extends XFCP_AdminNavigation
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_super_admins_only'] = ['type' => self::BOOL, 'default' => false];

        return $structure;
    }
}