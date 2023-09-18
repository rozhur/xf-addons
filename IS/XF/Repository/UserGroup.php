<?php

namespace ZD\IS\XF\Repository;

class UserGroup extends XFCP_UserGroup
{
    public function findUserGroupsForList()
    {
        if ($this->options()['zdisSortGroupsByPriority'])
        {
            $finder = $this->finder('XF:UserGroup')->order('display_style_priority', 'DESC');
        }
        else
        {
            $finder = parent::findUserGroupsForList();
        }

        return $finder;
    }

    public function rebuildDisplayStyleCache()
    {
        $cache = parent::rebuildDisplayStyleCache();

        $this->rebuildZdisDisplayStyleCache($cache);

        return $cache;
    }

    public function rebuildZdisDisplayStyleCache($cache)
    {
        $zdisCache = [];

        /** @var \ZD\IS\XF\Entity\UserGroup $userGroup */
        foreach ($cache AS $userGroupId => $userGroupData)
        {
            $userGroup = $this->em->find('XF:UserGroup', $userGroupId);
            if ($userGroup->zdis_username_css_class)
            {
                $zdisCache[$userGroupId]['zdis_username_css_class'] = $userGroup->zdis_username_css_class;
            }
        }

        \XF::registry()->set('zdisDisplayStyles', $zdisCache);

        return $zdisCache;
    }
}