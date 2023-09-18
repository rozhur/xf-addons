<?php

namespace ZD\SL\XF\Entity;

use XF\Mvc\Entity\Structure;
/**
 * COLUMNS
 * @property string $zdsl_seller
 */
class User extends XFCP_User
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdsl_seller'] = ['type' => self::BOOL, 'default' => false];

        return $structure;
    }
}
