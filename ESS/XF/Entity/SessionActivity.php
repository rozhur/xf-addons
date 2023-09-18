<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * @property bool $zdess_from_mobile
 */
class SessionActivity extends XFCP_SessionActivity
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_from_mobile'] = ['type' => self::BOOL, 'default' => false];

        return $structure;
    }
}