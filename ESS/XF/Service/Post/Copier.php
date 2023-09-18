<?php 

namespace ZD\ESS\XF\Service\Post;

class Copier extends XFCP_Copier
{
    public function copy($sourcePostsRaw)
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasNodePermission($this->target->node_id, 'manageAnyThread'))
        {
            return false;
        }

        return parent::copy($sourcePostsRaw);
    }
}
