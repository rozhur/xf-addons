<?php 

namespace ZD\ESS\XF\Admin\Controller;

use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class Forum extends XFCP_Forum
{
    public function actionAdd()
    {
        $this->assertAdminPermission('zdessCreateDeleteForum');

        return parent::actionAdd();
    }

    public function actionDelete(ParameterBag $params)
    {
        $this->assertAdminPermission('zdessCreateDeleteForum');

        return parent::actionDelete($params);
    }

    protected function nodeDelete(\XF\Entity\Node $node)
    {
        if (!\XF::visitor()->hasAdminPermission('zdessCreateDeleteForum'))
        {
            $node->getBehavior('XF:TreeStructured')->setOption('deleteChildAction', 'move');
        }

        parent::nodeDelete($node);
    }

    protected function saveTypeData(FormAction $form, \XF\Entity\Node $node, \XF\Entity\AbstractNode $data)
    {
        parent::saveTypeData($form, $node, $data);

        $input = ['zdess_forum_open' => $this->filter('zdess_forum_open', 'bool')];
        $form->setupEntityInput($data, $input);
    }
}
