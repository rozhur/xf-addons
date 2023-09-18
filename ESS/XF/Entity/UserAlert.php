<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

class UserAlert extends XFCP_UserAlert
{
    public function getUserId()
    {
        return $this->getValue('user_id') ?: $this->getUser()->user_id;
    }

    public function getUsername()
    {
        return $this->getValue('username') ?: $this->getUser()->username;
    }

    public function getUser()
    {
        if ($this->getValue('user_id'))
        {
            return $this->getRelation('User');
        }

        /** @var \ZD\ESS\XF\Repository\User $userRepo */
        $userRepo = $this->repository('XF:User');
        return $userRepo->getDefaultAlertUser();
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['user_id'] = true;
        $structure->getters['user'] = true;
        $structure->getters['username'] = true;

        return $structure;
    }
}