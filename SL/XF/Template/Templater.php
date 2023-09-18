<?php

namespace ZD\SL\XF\Template;

use ZD\SL\XF\Entity\User;

class Templater extends XFCP_Templater
{
    public function fnUserBanners($templater, &$escape, $user, $attributes = [])
    {
        /** @var User $user */
        $banners = parent::fnUserBanners($templater, $escape, $user, $attributes);
        if ($user->zdsl_seller && ($attributes['enable-seller-banner'] ?? false))
        {
            $class = $this->processAttributeToRaw($attributes, 'class', ' %s', true);
            $tag = $attributes['tag'] ?? 'em';
            $phrase = $this->phrase('zdsl_seller');
            $banners = "<$tag class=\"userBanner userBanner--accent userBanner--seller$class\"><span class=\"userBanner-before\"></span><strong>$phrase</strong><span class=\"userBanner-after\"></span></$tag>\n$banners";
        }
        return $banners;
    }

    public function fnUsernameLink($templater, &$escape, $user, $rich = false, $attributes = [])
    {
        /** @var User $user */
        $link = parent::fnUsernameLink($templater, $escape, $user, $rich, $attributes);
        if ($user->zdsl_seller && $rich && !($attributes['disable-seller-label'] ?? false))
        {
            $phrase = $this->phrase('zdsl_seller');
            $link .= " <span class=\"label label--accent label--seller\">$phrase</strong></span>";
        }
        return $link;
    }
}
