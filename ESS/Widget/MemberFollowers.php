<?php

namespace ZD\ESS\Widget;

use XF\Widget\AbstractWidget;
use ZD\ESS\XF\Entity\User;

class MemberFollowers extends AbstractWidget
{
    public function getDefaultTitle()
    {
        return \XF::phrase('followers');
    }

    public function getOptionsTemplate()
    {
        return '';
    }

    public function render()
    {
        if (($this->contextParams['user'] ?? false) instanceof User)
        {
            $user = $this->contextParams['user'];
            if (!$user->canViewFullProfile())
            {
                return '';
            }
        }
        else
        {
            $user = \XF::visitor();
        }

        $followers = $user->getFollowers(9);

        return $this->renderer('zdess_widget_member_followers', [
            'user' => $user,
            'followers' => $followers['followers'],
            'total' => $followers['total']
        ]);
    }
}