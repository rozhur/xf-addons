<?php

namespace ZD\ESS\XF\Admin\Controller;

class UserGroup extends XFCP_UserGroup
{
    protected function userGroupSaveProcess(\XF\Entity\UserGroup $userGroup)
    {
        /** @var \ZD\ESS\XF\Entity\UserGroup $userGroup */
        $form = parent::userGroupSaveProcess($userGroup);

        $form->setup(function() use ($userGroup)
        {
            $userGroup->zdess_disable_grouping = $this->filter('zdess_disable_grouping', 'bool');
            $userGroup->zdess_super_user_group = $this->filter('zdess_super_user_group', 'bool');
        });

        return $form;
    }
}