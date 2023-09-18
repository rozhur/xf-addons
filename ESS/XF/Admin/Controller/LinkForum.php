<?php 

namespace ZD\ESS\XF\Admin\Controller;

class LinkForum extends XFCP_LinkForum
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
