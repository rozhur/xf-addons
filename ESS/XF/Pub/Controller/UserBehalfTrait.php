<?php

namespace ZD\ESS\XF\Pub\Controller;

use XF\Pub\Controller\AbstractController;
use ZD\ESS\XF\Entity\User;

trait UserBehalfTrait
{
    public function setupBehalfPost(\ZD\ESS\XF\Entity\Post $post)
    {
        /** @var AbstractController $this */

        /** @var User $visitor */
        $visitor = \XF::visitor();
        if ($visitor->hasNodePermission($post->Thread->node_id, 'postBehalf'))
        {
            $userId = $this->filter('zdess_real_user_id', 'uint');

            /** @var User $user */
            $user = !$userId || $userId == $visitor->user_id ? null : $this->em()->find('XF:User', $userId);
            if (!$user)
            {
                if ($post->zdess_real_user_id)
                {
                    $realUser = $post->RealUser;
                    $post->user_id = $realUser->user_id;
                    $post->username = $realUser->username;
                    $post->zdess_real_user_id = 0;
                }
            }
            else
            {
                $post->setOption('toggle_user', false);
                if ($user->canBehalf())
                {
                    $post->user_id = $user->user_id;
                    $post->username = $user->username;
                    $post->zdess_real_user_id = $visitor->user_id;

                    $post->Thread->last_post_user_id = $user->user_id;
                    $post->Thread->last_post_username = $user->username;
                }
            }
        }
    }
}