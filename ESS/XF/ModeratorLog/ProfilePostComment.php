<?php 

namespace ZD\ESS\XF\ModeratorLog;

use XF\Mvc\Entity\Entity;

class ProfilePostComment extends XFCP_ProfilePostComment
{
    public function isLoggable(Entity $content, $action, \XF\Entity\User $actor)
    {
        switch (\XF::options()['zdessLoggingModeratorActions'])
        {
            case 'always':
                return true;
            case 'always_except_own_content':
                return $content['user_id'] != $actor->user_id;
            default:
                return parent::isLoggable($content, $action, $actor);
        }
    }

    public function isLoggableUser(\XF\Entity\User $actor)
    {
        return \XF::options()['zdessLoggingModeratorActions'] != 'default' || parent::isLoggableUser($actor);
    }
}
