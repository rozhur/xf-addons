<?php

namespace ZD\ESS\XF\Repository;

class AdminNavigation extends XFCP_AdminNavigation
{
    public function getNavigationCacheData()
    {
        $output = parent::getNavigationCacheData();

        foreach ($output as $navigationId => $value)
        {
            /** @var \ZD\ESS\XF\Entity\AdminNavigation $navigation */
            $navigation = $this->em->find('XF:AdminNavigation', $navigationId);
            $output[$navigationId]['zdess_super_admins_only'] = $navigation->zdess_super_admins_only;
        }

        return $output;
    }
}