<?php 

namespace ZD\ESS\XF\Admin\Controller;

use XF\Entity\NodeType;
use XF\Mvc\Reply\View;

class Node extends XFCP_Node
{
    public function actionAdd()
    {
        $view = parent::actionAdd();
        if ($view instanceof View)
        {
            $nodeTypes = $view->getParam('nodeTypes');

            /** @var NodeType $nodeType */
            foreach ($nodeTypes AS $nodeType)
            {
                if ($nodeType->node_type_id === 'Forum' && !\XF::visitor()->hasAdminPermission('zdessCreateDeleteForum'))
                {
                    unset($nodeTypes[$nodeType->node_type_id]);
                    break;
                }
            }

            $view->setParam('nodeTypes', $nodeTypes);
        }

        return $view;
    }
}
