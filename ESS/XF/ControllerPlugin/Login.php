<?php

namespace ZD\ESS\XF\ControllerPlugin;

use ZD\ESS\XF\Entity\User;

class Login extends XFCP_Login
{
    public function logoutVisitor()
    {
        $this->fromMobileUpdate();

        parent::logoutVisitor();
    }

    public function fromMobileUpdate()
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();
        if ($visitor->user_id)
        {
            $visitor->zdess_from_mobile = $visitor->Activity['zdess_from_mobile'] ?? false;
        }
    }
}