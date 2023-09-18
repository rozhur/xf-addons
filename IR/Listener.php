<?php

namespace ZD\IR;

use XF\Mvc\Entity\Entity;
use ZD\IR\Entity\CustomLink;

class Listener
{
    public static function appSetup(\XF\App $app)
    {
        $container = $app->container();

        $container['zdirCustomLinksIn'] = $app->fromRegistry('zdirCustomLinksIn', function(\XF\Container $c) {
            return $c['em']->getRepository('ZD\IR:CustomLink')->rebuildCustomLinkCache()['in'];
        });
        $container['zdirCustomLinksOut'] = $app->fromRegistry('zdirCustomLinksOut', function(\XF\Container $c) {
            return $c['em']->getRepository('ZD\IR:CustomLink')->rebuildCustomLinkCache()['out'];
        });

        $container['zdDisableCopyright'] = (bool) $app->config('zdDisableCopyright');
    }

    public static function entityPostDelete(Entity $entity)
    {
        if ($entity instanceof CustomLink)
        {
            self::repository()->rebuildCustomLinkCacheOnce();
        }
    }

    /** @return Repository\CustomLink */
    protected static function repository()
    {
        return \XF::app()->repository('ZD\IR:CustomLink');
    }
}