<?php

namespace ZD\TA\XFRM\Pub\Controller;

class ResourceItem extends XFCP_ResourceItem
{
    protected function setupResourceEdit(\XFRM\Entity\ResourceItem $resource)
    {
        $editor = parent::setupResourceEdit($resource);

        $updateFirstPost = $this->filter('zdta_update_first_post', 'bool');
        if ($updateFirstPost)
        {
            $resource->setOption('zdta_update_first_post', true);
        }

        return $editor;
    }
}
