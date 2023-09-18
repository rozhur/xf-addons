<?php 

namespace ZD\ESS\XF\Admin\Controller;

class Category extends XFCP_Category
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
