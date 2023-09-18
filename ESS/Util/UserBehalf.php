<?php

namespace ZD\ESS\Util;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class UserBehalf
{
    public static function toggleUser(Entity $behalf)
    {
        /** @var Entity|\ZD\ESS\Entity\UserBehalfInterface $behalf */
        if (!$behalf->zdess_real_user_id || $behalf->zdess_real_user_id == \XF::visitor()->user_id)
        {
            return;
        }

        $behalf->set('user_id', $behalf['user_id'] == $behalf->zdess_real_user_id ? $behalf->getPreviousValue('user_id') : $behalf->zdess_real_user_id);
    }

    public static function getStructure(Structure $structure)
    {
        $structure->columns['zdess_real_user_id'] = ['type' => Entity::UINT, 'default' => 0];

        $structure->relations['RealUser'] = [
            'entity' => 'XF:User',
            'type' => Entity::TO_ONE,
            'conditions' => 'zdess_real_user_id',
            'primary' => true
        ];

        $structure->options['toggle_user'] = true;

        return $structure;
    }
}