<?php

namespace ZD\ESS\XF\Admin\Controller;

class AdminNavigation extends XFCP_AdminNavigation
{
    protected function navigationSaveProcess(\XF\Entity\AdminNavigation $navigation)
    {
        /** @var \ZD\ESS\XF\Entity\AdminNavigation $navigation */
        $form = parent::navigationSaveProcess($navigation);

        $form->setup(function () use ($navigation)
        {
            $navigation->zdess_super_admins_only = $this->filter('zdess_super_admin_only', 'bool');
        });

        return $form;
    }
}