<?php

namespace ZD\ESS\XF\Repository;

use ZD\ESS\Util\MobileDetect;

class SessionActivity extends XFCP_SessionActivity
{
    public function updateSessionActivity($userId, $ip, $controller, $action, array $params, $viewState, $robotKey)
    {
        parent::updateSessionActivity($userId, $ip, $controller, $action, $params, $viewState, $robotKey);

        $fromMobile = MobileDetect::checkMobile();

        $identifier = intval($userId);
        if (!$identifier)
        {
            $identifier = \XF\Util\Ip::convertIpStringToBinary($ip);
            $query = 'UPDATE xf_session_activity SET zdess_from_mobile = ? WHERE unique_key = ?';
        }
        else
        {
            $query = 'UPDATE xf_session_activity SET zdess_from_mobile = ? WHERE user_id = ?';
        }

        $this->db()->query($query, [$fromMobile ? 1 : 0, $identifier]);
    }
}