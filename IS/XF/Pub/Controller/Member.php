<?php

namespace ZD\IS\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Redirect;

class Member extends XFCP_Member
{
    public function actionFollow(ParameterBag $params)
    {
        $reply = parent::actionFollow($params);
        if ($reply instanceof Redirect)
        {
            $wasFollowing = $reply->getJsonParams()['switchKey'] === 'follow';
            $reply->setJsonParam($wasFollowing ? 'removeClass' : 'addClass', 'button--link');
        }

        return $reply;
    }
}