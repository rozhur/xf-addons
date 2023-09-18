<?php

namespace ZD\ESS\Widget;

use XF\Widget\AbstractWidget;
use ZD\ESS\XF\Entity\User;

class MemberFollowing extends AbstractWidget
{
    public function getDefaultTitle()
    {
        return \XF::phrase('following');
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

        $following = $user->getFollowing(6);

        return $this->renderer('zdess_widget_member_following', [
            'user' => $user,
            'following' => $following['following'],
            'total' => $following['total']
        ]);
    }
}