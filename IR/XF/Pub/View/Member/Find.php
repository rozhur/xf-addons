<?php

namespace ZD\IR\XF\Pub\View\Member;

class Find extends XFCP_Find
{
    public function renderJson()
    {
        $results = [];
        $q = $this->params['q'];
        foreach ($this->params['users'] AS $user)
        {
            $avatarArgs = [$user, 'xxs', false, ['href' => '']];

            $results[] = [
                'id' => $user->username,
                'iconHtml' => $this->renderer->getTemplater()->func('avatar', $avatarArgs),
                'text' => strpos($user->custom_link, $q) === 0 ? $user->custom_link : $user->username,
                'q' => $q
            ];
        }

        return [
            'results' => $results,
            'q' => $q
        ];
    }
}