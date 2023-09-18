<?php 

namespace ZD\ESS\XF\Service\Post;

class Mover extends XFCP_Mover
{
    public function move($sourcePostsRaw)
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasNodePermission($this->target->node_id, 'manageAnyThread'))
        {
            return false;
        }

        return parent::move($sourcePostsRaw);
    }
}
