<?php 

namespace ZD\ESS\XF\Admin\Controller;

class Admin extends XFCP_Admin
{
    protected function adminAddEdit(\XF\Entity\Admin $admin)
    {
        $view = parent::adminAddEdit($admin);

        $modRepo = $this->repository('XF:Moderator');
        $availableModeratorPermissions = $modRepo->getModeratorPermissionData();

        $view->setParam('availableModeratorPermissions', $availableModeratorPermissions);

        return $view;
    }

    protected function adminSaveProcess(\XF\Entity\Admin $admin)
    {
        $form = parent::adminSaveProcess($admin);

        $availableModeratorPermissions = $this->filter('zdess_available_global_moderator_permissions', 'array');
        $availableModeratorPermissions += $this->filter('zdess_available_content_moderator_permissions', 'array');

        $form->setupEntityInput($admin, ['zdess_available_moderator_permissions' => $availableModeratorPermissions]);

        return $form;
    }
}
