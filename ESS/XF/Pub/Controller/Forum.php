<?php

namespace ZD\ESS\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

class Forum extends XFCP_Forum
{
    use UserBehalfTrait;

    public function actionForum(ParameterBag $params)
    {
        $view = parent::actionForum($params);

        if ($view instanceof View)
        {
            /** @var \ZD\ESS\XF\Entity\Forum $forum */
            $forum = $view->getParam('forum');

            $moderators = $forum->canViewModerators() ? $forum->Node->Moderators : [];

            $view->setParam('moderators', $moderators);
        }

        return $view;
    }

    public function actionQuickClose(ParameterBag $params)
    {
        $this->assertPostOnly();

        /** @var \ZD\ESS\XF\Entity\Forum $forum */
        $forum = $this->assertViewableForum($params->node_id);
        if (!$forum->canLockUnlock($error))
        {
            return $this->noPermission($error);
        }

        if ($forum->zdess_forum_open)
        {
            $forum->zdess_forum_open = false;
            $text = \XF::phrase('zdess_unlock_forum');
        }
        else
        {
            $forum->zdess_forum_open = true;
            $text = \XF::phrase('zdess_lock_forum');
        }

        $forum->save();

        $reply = $this->redirect($this->getDynamicRedirect());
        $reply->setJsonParams([
            'text' => $text,
            'forum_open' => $forum->zdess_forum_open
        ]);
        return $reply;
    }

    public function actionModerators(ParameterBag $params)
    {
        /** @var \ZD\ESS\XF\Entity\Forum $forum */
        $forum = $this->assertViewableForum($params->node_id);

        if (!$forum->canViewModerators($error))
        {
            return $this->error($error);
        }

        return $this->view('XF:Forum\Moderators', 'zdess_forum_moderator_list', [
            'forum' => $forum,
            'moderators' => $forum->Node->Moderators
        ]);
    }

    protected function setupThreadCreate(\XF\Entity\Forum $forum)
    {
        $creator = parent::setupThreadCreate($forum);

        /** @var \ZD\ESS\XF\Entity\Post $post */
        $post = $creator->getPost();
        $this->setupBehalfPost($post);

        return $creator;
    }
}