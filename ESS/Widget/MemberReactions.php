<?php

namespace ZD\ESS\Widget;

use XF\Entity\User;
use XF\Widget\AbstractWidget;

class MemberReactions extends AbstractWidget
{
    public function getDefaultTitle()
    {
        return \XF::phrase('reactions');
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

        return $this->renderer('zdess_widget_member_reactions', [
            'user' => $user
        ]);
    }
}