<?php 

namespace ZD\ESS\XF\ModeratorLog;

use XF\Mvc\Entity\Entity;

class Thread extends XFCP_Thread
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
                switch ($action)
                {
                    case 'lock':
                    case 'unlock':
                        if ($content['user_id'] == $actor->user_id)
                        {
                            return false;
                        }
                }
                return parent::isLoggable($content, $action, $actor);
        }
    }

    public function isLoggableUser(\XF\Entity\User $actor)
    {
        return \XF::options()['zdessLoggingModeratorActions'] != 'default' || parent::isLoggableUser($actor);
    }

    protected function getLogActionForChange(Entity $content, $field, $newValue, $oldValue)
    {
        /** @var \ZD\ESS\XF\Entity\Thread $content */

        switch ($field)
        {
            case 'zdess_disallow_open_discussion':
                return $newValue ? 'zdess_open_disallowed' : 'zdess_open_allowed';
        }

        return parent::getLogActionForChange($content, $field, $newValue, $oldValue);
    }
}
