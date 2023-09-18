<?php 

namespace ZD\ESS\XF\Service;

use XF\Entity\PermissionEntry;
use ZD\ESS\XF\Entity\Admin;

class UpdatePermissions extends XFCP_UpdatePermissions
{
    protected function writeEntry(\XF\Entity\Permission $permission, $value, \XF\Mvc\Entity\Entity $entry = null)
    {
        /** @var PermissionEntry $entry */
        if (($entry === null || $entry->permission_value === 'allow' || $entry->permission_value === 'deny') && $permission->Interface->is_moderator)
        {
            $visitor = \XF::visitor();
            /** @var Admin $admin */
            $admin = $visitor->Admin;
            if (!$admin->is_super_admin)
            {
                $permissions = $admin->zdess_available_moderator_permissions;
                if (!($permissions[$permission->permission_group_id][$permission->permission_id] ?? false))
                {
                    return null;
                }
            }
        }
        return parent::writeEntry($permission, $value, $entry);
    }
}
