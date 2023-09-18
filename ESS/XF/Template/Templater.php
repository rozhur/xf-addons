<?php

namespace ZD\ESS\XF\Template;

use XF\App;
use XF\Language;
use ZD\ESS\XF\Entity\User;

class Templater extends XFCP_Templater
{
    public function fnUsernameLink($templater, &$escape, $user, $rich = false, $attributes = [])
    {
        if ($user instanceof User && !\XF::visitor()->hasPermission('general', 'viewProfile') && !$user->canViewFullProfile())
        {
            $attributes['href'] = '';
        }
        return parent::fnUsernameLink($templater, $escape, $user, $rich, $attributes);
    }

    public function fnAvatar($templater, &$escape, $user, $size, $canonical = false, $attributes = [])
    {
        if ($user instanceof User)
        {
            if (!\XF::visitor()->hasPermission('general', 'viewProfile') && !$user->canViewFullProfile())
            {
                $attributes['href'] = '';
            }

            $attributes['class'] = (isset($attributes['class']) ? $attributes['class'] . ' ' : '') . 'avatar--style' . $user->display_style_group_id;
        }

        $result = parent::fnAvatar($templater, $escape, $user, $size, $canonical, $attributes);

        /** @var User $user */
        if (isset($attributes['context']) && $this->app->options()['showMessageOnlineStatus'] && $user && $user->isOnline())
        {
            $context = $attributes['context'];

            $class = 'avatar-online';
            if ($context === 'message')
            {
                $class = 'message-' . $class;
            }

            if ($user->is_from_mobile)
            {
                $title = $this->phrase('zdess_online_now_from_mobile');
                $class .= ' ' . $class . '--from-mobile';
            }
            else
            {
                $title = $this->phrase('online_now');
            }

            $result .= '<span class="' . $class . '" tabindex="0" data-xf-init="tooltip" data-trigger="auto" title="' . $title . '"></span>';
        }

        return $result;
    }

    public function fnUserTitle($templater, &$escape, $user, $withBanner = false, $attributes = [])
    {
        /** @var \XF\Entity\User $user */

        $escape = false;
        $userIsValid = ($user instanceof \XF\Entity\User);

        $userTitle = null;

        if ($userIsValid)
        {
            $customTitle = $user->custom_title;
            if ($customTitle)
            {
                $userTitle = htmlspecialchars($customTitle);
            }
        }

        if ($userTitle === null)
        {
            if ($withBanner && !empty($this->userBannerConfig['hideUserTitle']))
            {
                if (!$userIsValid)
                {
                    return '';
                }

                if (!empty($this->userBannerConfig['showStaff']) && $user->is_staff)
                {
                    return '';
                }

                if ($user->isMemberOf(array_keys($this->userBanners)))
                {
                    return '';
                }
            }

            if ($userIsValid)
            {
                $userTitle = $this->getDefaultUserTitleForUser($user);
            }
            else
            {
                $guestGroupId = \XF\Entity\User::GROUP_GUEST;

                $userTitle = \XF::phrase('user_group_user_title.' . $guestGroupId)->render('html', ['nameOnInvalid' => false]) ?: $this->groupStyles[$guestGroupId]['user_title'];
                if (!strlen($userTitle))
                {
                    return '';
                }
            }
        }

        if ($userTitle === null || !strlen($userTitle))
        {
            return '';
        }

        $class = $this->processAttributeToRaw($attributes, 'class', ' %s', true);

        if (!empty($attributes['tag']))
        {
            $tag = htmlspecialchars($attributes['tag']);
        }
        else
        {
            $tag = 'span';
        }

        unset($attributes['tag']);

        $unhandledAttrs = $this->processUnhandledAttributes($attributes);

        return "<{$tag} class=\"userTitle{$class}\" dir=\"auto\"{$unhandledAttrs}>{$userTitle}</{$tag}>";
    }

    public function getDefaultUserTitleForUser(\XF\Entity\User $user)
    {
        $groupId = $user->display_style_group_id;
        $phrase = \XF::phrase('user_group_user_title.' . $groupId)->render('html', ['nameOnInvalid' => false]) ?: $this->groupStyles[$groupId]['user_title'];
        if (strlen($phrase))
        {
            return $phrase;
        }
        else
        {
            foreach ($this->userTitleLadder AS $points => $title)
            {
                if ($user[$this->userTitleLadderField] >= $points)
                {
                    return \XF::phrase('user_title_ladder.' . $points)->render('html', ['nameOnInvalid' => false]) ?: $title;
                }
            }
        }

        return null;
    }

    public function fnUserActivity($templater, &$escape, $user)
    {
        $output = parent::fnUserActivity($templater, $escape, $user);
        if (!$output)
        {
            return $output;
        }

        if ($user->is_from_mobile)
        {
            $output .= ' <span role="presentation" aria-hidden="true">&middot;</span> <i class="fa fa-mobile-alt" data-xf-init="tooltip" data-trigger="hover focus touchclick" data-no-touch="false" data-original-title="' . $this->filterForAttr($this, $this->phrase('zdess_is_from_mobile'), $null) . '"></i>';
        }

        $escape = false;

        return $output;
    }

    public function fnDateDynamic($templater, &$escape, $dateTime, array $attributes = [])
    {
        $date = parent::fnDateDynamic($templater, $escape, $dateTime, $attributes);

        if (($attributes['is_from_mobile'] ?? false) == true)
        {
            $date .= ' <i class="fa fa-mobile-alt" style="padding-left: 3px" data-xf-init="tooltip" data-trigger="hover focus" data-no-touch="false" data-original-title="' . $this->filterForAttr($this, $this->phrase('zdess_is_from_mobile'), $null) . '"></i>';
        }

        return $date;
    }

    public function fnUserBanners($templater, &$escape, $user, $attributes = [])
    {
        /** @var \XF\Entity\User $user */
        $escape = false;

        if (!$user || !($user instanceof \XF\Entity\User) || !$user->user_id)
        {
            /** @var \XF\Repository\User $userRepo */
            $userRepo = $this->app->repository('XF:User');
            $user = $userRepo->getGuestUser();
        }

        $class = $this->processAttributeToRaw($attributes, 'class', ' %s', true);

        if (!empty($attributes['tag']))
        {
            $tag = htmlspecialchars($attributes['tag']);
        }
        else
        {
            $tag = 'em';
        }

        unset($attributes['tag']);

        $unhandledAttrs = $this->processUnhandledAttributes($attributes);

        $banners = [];
        $config = $this->userBannerConfig;

        if (!empty($config['showStaff']) && $user->is_staff)
        {
            $p = $this->phrase('staff_member');
            $banners['staff'] = "<{$tag} class=\"userBanner userBanner--staff{$class}\" dir=\"auto\"{$unhandledAttrs}>"
                . "<span class=\"userBanner-before\"></span><strong>{$p}</strong><span class=\"userBanner-after\"></span></{$tag}>";
        }

        $memberGroupIds = $user->secondary_group_ids;
        $memberGroupIds[] = $user->user_group_id;

        $primaryBanner = null;
        foreach ($this->userBanners AS $groupId => $banner)
        {
            if (!in_array($groupId, $memberGroupIds))
            {
                continue;
            }

            if ($banner['zdess_disable_grouping'] && $primaryBanner !== null)
            {
                continue;
            }

            if ($primaryBanner == null)
            {
                $primaryBanner = $banner;
            }

            $p = \XF::phrase('user_group_banner_text.' . $groupId)->render('html', ['nameOnInvalid' => false]) ?: $banner['title'];
            $banners[$groupId] = "<{$tag} class=\"userBanner {$banner['class']}{$class}\"{$unhandledAttrs}>"
                . "<span class=\"userBanner-before\"></span><strong>{$p}</strong><span class=\"userBanner-after\"></span></{$tag}>";
        }

        if (!$banners)
        {
            return '';
        }

        $count = count($banners);
        if (!empty($config['displayMultiple']))
        {
            return implode("\n", $banners);
        }
        else if (!empty($config['showStaffAndOther']) && isset($banners['staff']) && $count >= 2)
        {
            $staffBanner = $banners['staff'];
            unset($banners['staff']);
            return $staffBanner . "\n" . reset($banners);
        }
        else
        {
            return reset($banners);
        }
    }

    /**
     * @return string
     */
    public function getCurrentTemplateName()
    {
        return $this->currentTemplateName;
    }
}