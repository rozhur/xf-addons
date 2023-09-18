<?php

namespace ZD\ESS\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class ProfilePost extends XFCP_ProfilePost
{
    public function actionQuickStick(ParameterBag $params)
    {
        $this->assertPostOnly();

        /** @var \ZD\ESS\XF\Entity\ProfilePost $profilePost */
        $profilePost = $this->assertViewableProfilePost($params->profile_post_id);
        if (!$profilePost->canStickUnstick())
        {
            return $this->noPermission();
        }

        $profilePostRepo = $this->getProfilePostRepo();
        $profilePostFinder = $profilePostRepo->findProfilePostsOnProfile($profilePost->ProfileUser);

        if (!$profilePost->zdess_sticky)
        {
            /** @var \ZD\ESS\XF\Entity\ProfilePost $stickyProfilePost */
            $stickyProfilePost = $profilePostFinder->where('zdess_sticky', true)->fetchOne();

            if ($stickyProfilePost)
            {
                $stickyProfilePost->zdess_sticky = false;
                $stickyProfilePost->saveIfChanged();
            }

            $profilePost->zdess_sticky = true;
            $text = \XF::phrase('zdess_unstick');
        }
        else
        {
            $profilePost->zdess_sticky = false;
            $text = \XF::phrase('zdess_stick');
        }

        $profilePost->saveIfChanged();


        $profilePostFinder = $profilePostRepo->findProfilePostsOnProfile($profilePost->ProfileUser);
        $profilePostsTotal = $profilePostFinder->where('post_date', '>', $profilePost->post_date)->total();

        $page = floor($profilePostsTotal / $this->options()->messagesPerPage) + 1;

        $reply = $this->redirectPermanently(
            $this->buildLink('members', $profilePost->ProfileUser, ['page' => $page]) . '#profile-post-' . $profilePost->profile_post_id
        );

        $reply->setJsonParams([
            'text' => $text,
            'profile_post_stick' => $profilePost->zdess_sticky,
            ($profilePost->zdess_sticky ? 'addClass' : 'removeClass') => 'is-sticky'
        ]);
        return $reply;
    }
}