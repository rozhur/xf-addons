<?php

namespace ZD\IS\XF\Admin\Controller;

class UserGroup extends XFCP_UserGroup
{
    protected function userGroupAddEdit(\XF\Entity\UserGroup $userGroup)
    {
        $view = parent::userGroupAddEdit($userGroup);

        $displayStyles = $view->getParam('displayStyles');

        $displayStyleUsername = [];
        foreach ($displayStyles as $displayStyle)
        {
            if ($displayStyle !== 'userBanner userBanner--hidden')
            {
                $displayStyleUsername[] = str_replace('userBanner userBanner', 'username', $displayStyle);
            }
        }

        $view->setParam('displayStylesUsername', $displayStyleUsername);

        return $view;
    }

    protected function userGroupSaveProcess(\XF\Entity\UserGroup $userGroup)
    {
        /** @var \ZD\IS\XF\Entity\UserGroup $userGroup */
        $form = parent::userGroupSaveProcess($userGroup);

        $form->setupEntityInput($userGroup, $this->filter([
            'zdis_username_css_class' => 'str'
        ]));

        return $form;
    }
}