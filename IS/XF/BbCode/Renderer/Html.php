<?php

namespace ZD\IS\XF\BbCode\Renderer;

class Html extends XFCP_Html
{
    protected function getRenderedUser($content, int $userId)
    {
        if (!\XF::options()['zdisColoredTaggedUsers'])
        {
            return parent::getRenderedUser($content, $userId);
        }

        $user = \XF::em()->find('XF:User', $userId);
        if (!$user)
        {
            return parent::getRenderedUser($content, $userId);
        }

        return $this->templater->func('username_link', [$user, true, ['username' => $content]]);
    }
}