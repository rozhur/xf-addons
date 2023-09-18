<?php

namespace ZD\ESS\XF\Pub\Controller;

class Post extends XFCP_Post
{
    use UserBehalfTrait;

    protected function setupPostEdit(\XF\Entity\Post $post)
    {
        $editor = parent::setupPostEdit($post);

        /** @var \ZD\ESS\XF\Entity\Post $post */
        $this->setupBehalfPost($post);

        return $editor;
    }
}