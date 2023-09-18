<?php

namespace ZD\IS\XF\Template;

use XF\App;
use XF\Language;
use XF\Mvc\Entity\AbstractCollection;

class Templater extends XFCP_Templater
{
    public function fnUserActivity($templater, &$escape, $user)
    {
        if ($this->func('property', ['zdisWrapDetailedUserActivityInTooltip'], $escape))
        {
            if (!$user instanceof \XF\Entity\User || !$user->user_id)
            {
                return '';
            }

            if (!$user->canViewOnlineStatus())
            {
                return '';
            }

            $activityDetail = null;
            if ($user->canViewCurrentActivity() && $user->Activity)
            {
                if ($user->Activity->description)
                {
                    $activityDetail = \XF::escapeString($user->Activity->description);
                    if ($user->Activity->item_title)
                    {
                        $title = \XF::escapeString($user->Activity->item_title);
                        $url = \XF::escapeString($user->Activity->item_url);

                        $activityDetail .= " <em><a href=\"{$url}\" dir=\"auto\">{$title}</a></em>";
                    }

                    if ($user->Activity->view_state == 'error' && \XF::visitor()->canBypassUserPrivacy())
                    {
                        $activityDetail .= ' <span role="presentation" aria-hidden="true">&middot;</span> ';
                        $activityDetail .= '<i class="fa fa-exclamation-triangle u-muted" title="' . $this->filterForAttr($this,$this->phrase('viewing_an_error'), $null) . '" aria-hidden="true"></i>';
                        $activityDetail .= ' <span class="u-srOnly">' . $this->phrase('viewing_an_error') . '</span>';
                    }
                }
            }

            $output = $user->isOnline() ? $this->phrase('online_now') : $this->fnDateDynamic($this, $escape, $user->last_activity);
            if ($activityDetail)
            {
                $output = '<span class="js-activity"><span data-xf-init="element-tooltip" data-maintain="true" data-trigger="hover focus touchclick" data-click-hide="true" data-no-touch="false" data-element="< .js-activity | .js-activityDetail">' . $output . '</span>';
                $output .= ' <span class="js-activityDetail" style="display: none">' . $activityDetail . '</span></span>';
            }

            $escape = false;
        }
        else
        {
            $output = parent::fnUserActivity($templater, $escape, $user);
        }

        return $output;
    }

    public function fnUsernameLink($templater, &$escape, $user, $rich = false, $attributes = [])
    {
        $class = $attributes['class'] ?? '';

        $classes = parent::fnUsernameClasses($this, $null, $user, $rich);
        $class .= ($class ? ' ' : '') . $classes;

        if ($rich)
        {
            if (!$user || empty($user['user_id']))
            {
                $displayGroupId = \XF\Entity\User::GROUP_GUEST;
            }
            else
            {
                if (!empty($user['display_style_group_id']))
                {
                    $displayGroupId = $user['display_style_group_id'];
                }
                else
                {
                    $displayGroupId = 0;
                }
            }

            if ($displayGroupId)
            {
                if (!empty($this->groupStyles[$displayGroupId]['zdis_username_css_class']))
                {
                    $class .= ($class ? ' ' : '') . $this->groupStyles[$displayGroupId]['zdis_username_css_class'];
                }
            }
        }

        $attributes['class'] = $class;

        return parent::fnUsernameLink($templater, $escape, $user, $rich, $attributes);
    }

    public function fnUsernameClasses($templater, &$escape, $user, $includeGroupStyling = true)
    {
        return '';
    }
}