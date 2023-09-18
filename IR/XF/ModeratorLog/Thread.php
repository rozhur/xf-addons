<?php 

namespace ZD\IR\XF\ModeratorLog;

use XF\Mvc\Entity\Entity;

class Thread extends XFCP_Thread
{
    public function isLoggable(Entity $content, $action, \XF\Entity\User $actor)
    {
        if (!parent::isLoggable($content, $action, $actor))
        {
            return false;
        }

        if ($action == 'zdir_custom_link' && $actor->user_id == $content->user_id)
        {
            return false;
        }

        return true;
    }

    protected function getLogActionForChange(Entity $content, $field, $newValue, $oldValue)
    {
        /** @var \ZD\ESS\XF\Entity\Thread $content */

        switch ($field)
        {
            case 'zdir_custom_link':
                return ['zdir_custom_link', ['old' => $oldValue]];
        }

        return parent::getLogActionForChange($content, $field, $newValue, $oldValue);
    }
}
