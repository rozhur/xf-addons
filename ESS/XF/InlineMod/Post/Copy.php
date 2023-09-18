<?php 

namespace ZD\ESS\XF\InlineMod\Post;

use XF\Mvc\Entity\Entity;

class Copy extends XFCP_Copy
{
    protected function canApplyToEntity(Entity $entity, array $options, &$error = null)
    {
        return parent::canApplyToEntity($entity, $options, $error) && ($options['node_id'] == 0 || \XF::visitor()->hasNodePermission($options['node_id'], 'manageAnyThread'));
    }
}
