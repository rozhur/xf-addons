<?php

namespace ZD\ESS\XF;

class AdminNavigation extends XFCP_AdminNavigation
{
    protected function setupFilteredRecurse($rootId, $depth, array $map, array &$filtered)
    {
        $validChildren = parent::setupFilteredRecurse($rootId, $depth, $map, $filtered);

        $this->filterSuperAdmin($validChildren, $filtered);

        return $validChildren;
    }

    protected function filterSuperAdmin($validChildren, array &$filtered)
    {
        $visitor = $this->visitor;
        foreach ($validChildren as $id)
        {
            $entry = $this->entries[$id];
            if (!isset($entry['zdess_super_admins_only']))
            {
                $navigationRepo = \XF::repository('XF:AdminNavigation');
                $navigationRepo->rebuildNavigationCache();
                $this->filterSuperAdmin($validChildren, $filtered);
                return;
            }

            if ($entry['zdess_super_admins_only'] && !$visitor->is_super_admin)
            {
                unset($filtered[$id]);
            }
        }
    }
}