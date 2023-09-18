<?php

namespace ZD\IR\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use ZD\IR\Util\CustomLink;

class Thread extends XFCP_Thread
{
    public function actionEdit(ParameterBag $params)
    {
        $view = parent::actionEdit($params);
        if ($view instanceof \XF\Mvc\Reply\View)
        {
            $link = $this->router()->buildLink('threads', $view->getParam('thread'));
            $link = substr($link, strlen($this->request()->getBasePath()));

            $view->setParam('link', trim($link, '/'));
        }

        return $view;
    }

    protected function setupThreadEdit(\XF\Entity\Thread $thread)
    {
        /** @var \ZD\IR\XF\Entity\Thread $thread */
        if ($thread->canEditCustomLink())
        {
            $link = $this->filter('zdir_custom_link', 'str');

            $existingLink = substr($this->buildLink('threads', $thread), 1);
            if ($existingLink !== $link)
            {
                $thread->zdir_custom_link = $link;
            }
        }
        return parent::setupThreadEdit($thread);
    }

    public function actionValidateCustomLink(ParameterBag $params)
    {
        $this->assertPostOnly();

        $link = $this->filter('content', 'str');

        $errors = [];

        $thread = $this->em()->find('XF:Thread', $params->thread_id);
        if ($thread && !CustomLink::verifyCustomLink($thread, $link))
        {
            $errors = $thread->getErrors();
        }

        $view = $this->view('XF:Misc\ValidateCustomLink');
        $view->setJsonParams([
            'inputValid' => !count($errors),
            'inputErrors' => $errors,
            'validatedValue' => $link
        ]);
        return $view;
    }
}