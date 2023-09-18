<?php

namespace ZD\ESS\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Thread extends XFCP_Thread
{
    use UserBehalfTrait;

    protected function setupThreadReply(\XF\Entity\Thread $thread)
    {
        $replier = parent::setupThreadReply($thread);
        $post = $replier->getPost();

        /** @var \ZD\ESS\XF\Entity\Post $post */
        $this->setupBehalfPost($post);

        return $replier;
    }

    protected function setupThreadEdit(\XF\Entity\Thread $thread)
    {
        $editor = parent::setupThreadEdit($thread);

        /** @var \ZD\ESS\XF\Entity\Thread $thread */
        if ($thread->canLockUnlockAny())
        {
            $thread->zdess_disallow_open_discussion = $this->filter('zdess_disallow_open_discussion', 'bool');
        }

        return $editor;
    }

    protected function setupThreadMove(\XF\Entity\Thread $thread, \XF\Entity\Forum $forum)
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasNodePermission($forum->node_id, 'manageAnyThread'))
        {
            throw $this->exception($this->noPermission());
        }

        return parent::setupThreadMove($thread, $forum);
    }
}