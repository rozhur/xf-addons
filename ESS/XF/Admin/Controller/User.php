<?php

namespace ZD\ESS\XF\Admin\Controller;

class User extends XFCP_User
{
    protected function userSaveProcess(\XF\Entity\User $user)
    {
        $form = parent::userSaveProcess($user);

        /** @var \ZD\ESS\XF\Entity\User $user */
        $input = $this->filter([
            'user' => ['zdess_behalf_criteria' => 'array'],
            'option' => ['zdess_show_reg_date' => 'bool']
        ]);

        $form->setupEntityInput($user, $input['user']);
        $form->setupEntityInput($user->Option, $input['option']);

        return $form;
    }

    protected function getSearcherParams(array $extraParams = [])
    {
        /** @var \XF\Repository\Reaction $reactionRepo */
        $reactionRepo = $this->repository('XF:Reaction');
        return parent::getSearcherParams($extraParams) +
            [
                'reactions' => $reactionRepo->findReactionsForList(true)
            ];
    }
}