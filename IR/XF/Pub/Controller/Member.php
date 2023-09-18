<?php

namespace ZD\IR\XF\Pub\Controller;

use XF\Entity\User;
use XF\Mvc\ParameterBag;
use ZD\IR\Util\CustomLink;

class Member extends XFCP_Member
{
    public function actionValidateCustomLink(ParameterBag $params)
    {
        $this->assertPostOnly();

        $link = $this->filter('content', 'str');

        $errors = [];

        $visitor = \XF::visitor();

        $user = $params->user_id != $visitor->user_id ? $this->em()->find('XF:User', $params->user_id) : $visitor;
        if ($user && !CustomLink::verifyCustomLink($user, $link))
        {
            $errors = $user->getErrors();
        }

        $view = $this->view('XF:Misc\ValidateCustomLink');
        $view->setJsonParams([
            'inputValid' => !count($errors),
            'inputErrors' => $errors,
            'validatedValue' => $link
        ]);
        return $view;
    }

    public function actionFind()
    {
        $q = ltrim($this->filter('q', 'str', ['no-trim']));

        if ($q !== '' && utf8_strlen($q) >= 2)
        {
            /** @var \XF\Finder\User $userFinder */
            $userFinder = $this->finder('XF:User');

            $routePrefix = \XF::app()->router('public')->buildLink('members');
            $routePrefix = substr($routePrefix, strlen(\XF::app()->request()->getBasePath()) + 1);

            if (@preg_match('/^' . preg_quote($routePrefix) . '\d+?/', $q))
            {
                $userFinder->where('user_id', 'like', $userFinder->escapeLike(substr($q, strlen($routePrefix)), '?%'));
            }
            else
            {
                $input = $userFinder->escapeLike($q, '?%');
                $userFinder->whereOr([
                    ['username', 'like', $input],
                    ['zdir_custom_link', 'like', $input],
                ]);
            }

            $users = $userFinder
                ->isValidUser(true)
                ->fetch(10);
        }
        else
        {
            $users = [];
            $q = '';
        }

        $viewParams = [
            'q' => $q,
            'users' => $users
        ];
        return $this->view('XF:Member\Find', '', $viewParams);
    }

    protected function memberSaveProcess(User $user)
    {
        /** @var \ZD\IR\XF\Entity\User $user */
        $form = parent::memberSaveProcess($user);

        $form->setup(function() use ($user)
        {
            $input = $this->filter(['profile' => ['zdir_custom_link' => 'str']]);
            $link = $input['profile']['zdir_custom_link'];

            $user->zdir_custom_link = $link;
        });

        return $form;
    }
}