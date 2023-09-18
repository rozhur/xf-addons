<?php

namespace ZD\IS;

use XF\Container;
use ZD\IS\XF\Entity\Admin;

class Listener
{
    public static function templaterSetup(\XF\Container $container, \XF\Template\Templater &$templater)
    {
        $container['zdisDisplayStyles'] = \XF::app()->fromRegistry('zdisDisplayStyles',
            function(Container $c) { return $c['em']->getRepository('XF:UserGroup')->rebuildZdisDisplayStyleCache($c['displayStyles']); }
        );

        $zdisDisplayStyles = $container['zdisDisplayStyles'];
        $groupStyles = $container['displayStyles'];
        foreach ($groupStyles as $groupStyleId => &$groupStyle)
        {
            if (isset($zdisDisplayStyles[$groupStyleId]))
            {
                $groupStyle += $zdisDisplayStyles[$groupStyleId];
            }
        }

        $templater->setGroupStyles($groupStyles);
    }

    public static function templaterGlobalData(\XF\App $app, array &$data, $reply)
    {
        if ($app instanceof \XF\Admin\App && !($app['zdisIgnoreAdminStyle'] ?? false))
        {
            $visitor = \XF::visitor();
            if ($visitor->is_admin)
            {
                /** @var Admin $admin */
                $admin = $visitor->Admin;

                $style = $app->style($admin->zdis_admin_style_id);
                $app->templater()->setStyle($style);

                $data['style'] = $app->templater()->getStyle();
            }
        }
    }
}