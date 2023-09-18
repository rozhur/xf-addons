<?php

namespace ZD\SL\XF\Admin\Controller;

class User extends XFCP_User
{
    protected function userSaveProcess(\XF\Entity\User $user)
    {
        $form = parent::userSaveProcess($user);

        $input = $this->filter([
            'user' => [
                'zdsl_seller' => 'bool'
            ]
        ]);

        $form->setupEntityInput($user, $input['user']);

        return $form;
    }
}
