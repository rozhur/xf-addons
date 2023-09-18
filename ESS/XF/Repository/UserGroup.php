<?php

namespace ZD\ESS\XF\Repository;

class UserGroup extends XFCP_UserGroup
{
    public function findUserGroupsForList()
    {
        $finder = parent::findUserGroupsForList();
        if (!\XF::visitor()->canChooseSuperUserGroup())
        {
            $app = $this->app();
            if (!($app instanceof \XF\Admin\App) || $app['zdessController'] === 'XF:Warning' || !$app['zdessUserCriteriaSetup'] && $app['zdessController'] !== 'XF:Advertising')
            {
                $finder->where('zdess_super_user_group', '=', false);
            }
        }

        return $finder;
    }

    public function getUserBannerCacheData()
    {
        $cache = parent::getUserBannerCacheData();

        /** @var \ZD\ESS\XF\Entity\UserGroup $userGroup */
        foreach ($cache AS $userGroupId => $userGroupCache)
        {
            $userGroup = $this->em->find('XF:UserGroup', $userGroupId);
            $cache[$userGroupId]['zdess_disable_grouping'] = $userGroup->zdess_disable_grouping;
        }

        return $cache;
    }
}