<?php 

namespace ZD\IR\XF\Admin\Controller;

use XF\Mvc\ParameterBag;

class Login extends XFCP_Login
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        parent::preDispatchController($action, $params);

        if (!\XF::visitor()->user_id)
        {
            $session = $this->app()['session.public'];

            $userRepo = $this->repository('XF:User');
            $sessionUserId = $session->userId;
            $user = $userRepo->getVisitor($sessionUserId);

            if ($user->user_id && $user->user_id == $sessionUserId)
            {
                $userPasswordDate = $user->Profile ? $user->Profile->password_date : 0;
                if ($session->passwordDate != $userPasswordDate)
                {
                    $session->logoutUser();
                    $user = $userRepo->getVisitor(0);
                }
            }

            if (!$user->user_id || !$user->is_admin)
            {
                throw $this->exception($this->redirect($this->app()->router('public')->buildLink('index')));
            }
        }
    }
}
