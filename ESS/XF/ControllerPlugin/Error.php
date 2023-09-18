<?php

namespace ZD\ESS\XF\ControllerPlugin;

class Error extends XFCP_Error
{
    public function actionNoPermission($message)
    {
        $reply = parent::actionNoPermission($message);

        if ($reply instanceof \XF\Mvc\Reply\View && $reply->getTemplateName() === 'login')
        {
            if (!$message)
            {
                $message = \XF::phrase('do_not_have_permission');
            }

            return $this->error($message, 403);
        }

        return $reply;
    }
}