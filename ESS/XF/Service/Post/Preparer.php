<?php

namespace ZD\ESS\XF\Service\Post;

use ZD\ESS\XF\Entity\Post;

class Preparer extends XFCP_Preparer
{
    protected function getMessagePreparer($format = true)
    {
        $preparer = parent::getMessagePreparer($format);

//        /** @var Post $post */
//        $post = $this->post;
//        if ($post->isFirstPost() && $post->canPostEmptyMessage())
//        {
//            $preparer->setConstraint('allowEmpty', true);
//        }

        return $preparer;
    }
}