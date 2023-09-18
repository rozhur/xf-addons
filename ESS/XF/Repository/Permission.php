<?php 

namespace ZD\ESS\XF\Repository;

use ZD\ESS\XF\Entity\Admin;

class Permission extends XFCP_Permission
{
    public function findPermissionsForList()
    {
        $finder = parent::findPermissionsForList();
        $visitor = \XF::visitor();

        /** @var Admin $admin */
        $admin = $visitor->Admin;
        if (!$admin->is_super_admin)
        {
            $permissions = [];

            foreach ($admin->zdess_available_moderator_permissions AS $interfaceGroup)
            {
                foreach ($interfaceGroup AS $permissionId => $value)
                {
                    if ($value)
                    {
                        $permissions[] = $permissionId;
                    }
                }
            }

            $finder->whereOr([['permission_id', $permissions], ['Interface.is_moderator', false]]);
        }

        return $finder;
    }
}
