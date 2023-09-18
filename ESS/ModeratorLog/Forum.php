<?php

namespace ZD\ESS\ModeratorLog;

use XF\Entity\ModeratorLog;
use XF\ModeratorLog\AbstractHandler;
use XF\Mvc\Entity\Entity;

class Forum extends AbstractHandler
{
    public function isLoggable(Entity $content, $action, \XF\Entity\User $actor)
    {
        switch (\XF::options()['zdessLoggingModeratorActions'])
        {
            case 'always':
            case 'always_except_own_content':
                return true;
            default:
                return parent::isLoggable($content, $action, $actor);
        }
    }

    public function isLoggableUser(\XF\Entity\User $actor)
    {
        return \XF::options()['zdessLoggingModeratorActions'] != 'default' || parent::isLoggableUser($actor);
    }

    protected function getLogActionForChange(Entity $content, $field, $newValue, $oldValue)
    {
        /** @var \ZD\ESS\XF\Entity\Forum $content */

        switch ($field)
        {
            case 'zdess_forum_open':
                return $newValue ? 'zdess_unlock' : 'zdess_lock';
        }

        return false;
    }

    protected function setupLogEntityContent(ModeratorLog $log, Entity $content)
    {
        /** @var \ZD\ESS\XF\Entity\Forum $content */
        $log->content_username = \XF::options()['boardTitle'];
        $log->content_title = $content->title;
        $log->content_url = \XF::app()->router('public')->buildLink('nopath:forums', $content);
        $log->content_type = 'forum';
        $log->content_id = $content->node_id;
        $log->discussion_content_id = $content->node_id;
        $log->discussion_content_type = 'forum';
    }

    public function getContentTitle(ModeratorLog $log)
    {
        return \XF::phrase('forum', [
            'title' => $log->content_title
        ])->render('raw');
    }
}