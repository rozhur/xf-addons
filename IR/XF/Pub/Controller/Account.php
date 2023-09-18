<?php

namespace ZD\IR\XF\Pub\Controller;

use XF\Mvc\Reply\View;

class Account extends XFCP_Account
{
    public function actionAccountDetails()
    {
        $view = parent::actionAccountDetails();

        if ($view instanceof View)
        {
            $link = $this->router()->buildLink('members', \XF::visitor());
            $link = substr($link, strlen($this->request()->getBasePath()));

            $view->setParam('link', trim($link, '/'));
        }

        return $view;
    }

    protected function accountDetailsSaveProcess(\XF\Entity\User $visitor)
    {
        $form = parent::accountDetailsSaveProcess($visitor);

        if ($visitor->canEditCustomLink())
        {

            $form->setup(function() use ($visitor)
            {
                $link = $this->filter('zdir_custom_link', 'str');

                $existingLink = substr($this->buildLink('members', $visitor), 1);
                if ($existingLink === $link)
                {
                    return;
                }

                $visitor->zdir_custom_link = $link;
            });
        }

        return $form;
    }
}