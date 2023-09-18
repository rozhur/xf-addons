<?php

namespace ZD\IC\XF\ControllerPlugin;

use ZD\IC\Listener;

class Login extends XFCP_Login
{
    public function logoutVisitor()
    {
        parent::logoutVisitor();

        Listener::emitMembersOnlineWidget();
    }
}