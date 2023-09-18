<?php

namespace ZD\ESS\XF\Pub\Controller;

use XF\Entity\ModeratorContent;
use XF\Entity\Node;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Error;
use XF\Mvc\Reply\Exception;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;
use XF\Repository\Moderator;
use ZD\ESS\XF\Entity\Reaction;
use ZD\ESS\XF\Entity\User;
use ZD\IR\Util\CustomLink;

class Member extends XFCP_Member
{
    public function actionView(ParameterBag $params)
    {
        try
        {
            $view = parent::actionView($params);
            if (!($view instanceof View))
            {
                return $view;
            }

            /** @var User $user */
            $user = $view->getParam('user');

            /** @var AbstractCollection $profilePosts */
            $profilePosts = $view->getParam('profilePosts');

            $profilePostRepo = $this->getProfilePostRepo();

            /** @var \ZD\ESS\XF\Entity\ProfilePost $stickyProfilePost */
            $stickyProfilePost = $profilePostRepo->findProfilePostsOnProfile($user)->where('zdess_sticky', true)->fetchOne();

            $viewParams = [
                'moderatedForums' => $this->getModeratedForums($user),
                'reactions' => $user->reaction_count,
                'profilePosts' => $profilePosts,
                'stickyProfilePost' => $stickyProfilePost,
                'noView' => false
            ];

            $view->setParams($viewParams);

            return $view;
        }
        catch (Exception $exception)
        {
            $reply = $exception->getReply();

            if ($reply->getResponseCode() !== 403 || !($reply instanceof Error))
            {
                throw $exception;
            }

            foreach ($reply->getErrors() as $error)
            {
                if ($error instanceof \XF\Phrase && $error->getName() !== 'do_not_have_permission')
                {
                    /** @var User $user */
                    $user = $this->em()->find('XF:User', $params->user_id);
                    $viewParams = [
                        'user' => $user,
                        'moderatedForums' => $this->getModeratedForums($user),
                        'noView' => true,
                        'noViewErrorPhrase' => $error
                    ];
                    return $this->view('XF:Member\View', 'member_view', $viewParams);
                }
            }

            throw $exception;
        }
    }

    protected function getModeratedForums(User $user)
    {
        if (!$user->is_moderator || $user->is_super_moderator)
        {
            return [];
        }

        /** @var ModeratorContent[] $moderatorContent */
        $moderatorContent = $this->finder('XF:ModeratorContent')
            ->where('user_id', $user->user_id)
            ->fetch();

        /** @var \XF\Repository\Node $nodeRepo */
        $nodeRepo = \XF::repository('XF:Node');
        /** @var Node[] $nodes */
        $nodes = $nodeRepo->getFullNodeListCached('NodeModerator')->toArray();

        $content = [];
        foreach ($moderatorContent as $c)
        {
            $node = $nodes[$c->content_id];
            if ($node->node_type_id !== 'Forum')
            {
                continue;
            }

            /** @var \ZD\ESS\XF\Entity\Forum $forum */
            $forum = $node->Data;
            if (!$forum->canViewModerators())
            {
                continue;
            }

            $content[] = [
                'title' => $node->title,
                'link' => $node->getContentUrl()
            ];
        }

        return $content;
    }

    public function actionUsernameHistoryDelete(ParameterBag $params)
    {
        /** @var User $user */
        $user = $this->assertViewableUser($params->user_id, [], true);

        if (!$user->canDeleteUsernameHistory())
        {
            return $this->noPermission();
        }

        if ($this->isPost())
        {
            if ($this->filter('hard_delete', 'bool'))
            {
                /** @var User $visitor */
                $visitor = \XF::visitor();
                if (!$visitor->canHardDeleteUsernameHistory())
                {
                    return $this->noPermission();
                }

                $user->username_date = 0;
                $user->username_date_visible = false;
                $user->save();

                $this->app()->db()->delete('xf_username_change', 'user_id = ?', [$user->user_id]);
            }
            else
            {
                $this->app()->db()->update('xf_username_change', ['visible' => 0], 'user_id = ?', [$user->user_id]);
            }

            return $this->redirect($this->buildLink('members', $user));
        }
        else
        {
            return $this->view('XF:Member\UsernameHistoryDelete', 'zdess_delete_username_history', ['user' => $user]);
        }
    }

    public function actionValidateCustomTitle(ParameterBag $params)
    {
        $this->assertPostOnly();

        $title = $this->filter('content', 'str');

        $errors = [];

        $visitor = \XF::visitor();

        /** @var User $user */
        $user = $params->user_id != $visitor->user_id ? $this->em()->find('XF:User', $params->user_id) : $visitor;
        if ($user && !$user->verifyCustomTitle($title))
        {
            $errors = $user->getErrors();
        }

        $view = $this->view('XF:Misc\ValidateCustomTitle');
        $view->setJsonParams([
            'inputValid' => !count($errors),
            'inputErrors' => $errors,
            'validatedValue' => $title
        ]);
        return $view;
    }

    public function actionReactions(ParameterBag $params)
    {
        /** @var User $user */
        $user = $this->assertViewableUser($params->user_id);

        return $this->view('XF:Member\Reactions', 'zdess_member_reactions', [
            'user' => $user,
            'reactions' => $user->reaction_count
        ]);
    }

    public function userBanAddEdit(\XF\Entity\User $user)
    {
        $reply = parent::userBanAddEdit($user);

        if ($reply instanceof Error && empty($reply->getErrors()[0]))
        {
            $reply->setResponseCode(403);
            $reply->setErrors(\XF::phraseDeferred('do_not_have_permission'), false);
        }

        return $reply;
    }
}