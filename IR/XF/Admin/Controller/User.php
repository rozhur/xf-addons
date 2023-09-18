<?php

namespace ZD\IR\XF\Admin\Controller;

class User extends XFCP_User
{
    protected function userSaveProcess(\XF\Entity\User $user)
    {
        /** @var \ZD\IR\XF\Entity\User $user */
        $form = parent::userSaveProcess($user);

        $form->setup(function() use ($user)
        {
            $input = $this->filter(['profile' => ['zdir_custom_link' => 'str']]);
            $link = $input['profile']['zdir_custom_link'];

            $user->zdir_custom_link = $link;
        });

        return $form;
    }
}