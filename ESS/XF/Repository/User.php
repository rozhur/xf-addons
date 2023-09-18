<?php

namespace ZD\ESS\XF\Repository;

class User extends XFCP_User
{
    public function getDefaultAlertUser()
    {
        return $this->getUserByIdOrName($this->app()->options()['zdessDefaultAlertUser']) ?: $this->getGuestUser();
    }

    public function getUserByIdOrName($input)
    {
        $finder = $this->finder('XF:User');
        if (is_numeric($input))
        {
            $finder->where('user_id', intval($input));
        }
        else if (is_string($input))
        {
            $finder->where('username', strval($input));
        }
        else
        {
            return null;
        }

        return $finder->fetchOne();
    }
}