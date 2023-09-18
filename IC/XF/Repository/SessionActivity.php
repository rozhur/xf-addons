<?php

namespace ZD\IC\XF\Repository;

use ZD\IC\Listener;

class SessionActivity extends XFCP_SessionActivity
{
    public function updateSessionActivity($userId, $ip, $controller, $action, array $params, $viewState, $robotKey)
    {
//        $visitor = \XF::visitor();
//
//        $onlineCutOff = time() - $this->app()->options()->onlineStatusTimeout * 60;
//        $emit = $visitor->Activity && $visitor->Activity->view_date < $onlineCutOff;
//
        parent::updateSessionActivity($userId, $ip, $controller, $action, $params, $viewState, $robotKey);
//
//        if ($emit)
//        {
//            Listener::emitMembersOnlineWidget();
//        }
    }
}