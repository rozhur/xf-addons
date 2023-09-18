<?php 

namespace ZD\ESS\XF\Admin\Controller;

class SearchForum extends XFCP_SearchForum
{
    protected function nodeDelete(\XF\Entity\Node $node)
    {
        if (!\XF::visitor()->hasAdminPermission('zdessCreateDeleteForum'))
        {
            $node->getBehavior('XF:TreeStructured')->setOption('deleteChildAction', 'move');
        }

        parent::nodeDelete($node);
    }
}
