<?php

namespace ZD\ESS;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View;
use XF\Pub\App;
use ZD\ESS\XF\Entity\Admin;
use ZD\ESS\XF\Entity\ProfilePost;
use ZD\ESS\XF\Entity\Reaction;

class Listener
{
    public static function appSetup(\XF\App $app)
    {
        $app->container()->set('zdessUserCriteriaSetup', false);
        $app->container()->set('zdessController', null);
        $app->container()->set('zdessAction', null);
    }

    public static function visitorSetup(\XF\Entity\User &$visitor)
    {
        $app = $visitor->app();
        if (!($app->container('config')['zdessAllowDebugAndDevelopmentModeWithoutPermissions'] ?? false) && !$visitor->is_super_admin)
        {
            if (!$visitor->hasAdminPermission('zdessDevelopment'))
            {
                $app->container('config')['development']['enabled'] = false;

                \XF::$developmentMode = false;
            }

            if (!$visitor->hasAdminPermission('zdessDebug'))
            {
                $app->container('config')['debug'] = false;

                \XF::$debugMode = false;
            }
        }
    }

    public static function preDispatch(\XF\Mvc\Dispatcher $dispatcher, \XF\Mvc\RouteMatch $routeMatch)
    {
        $app = \XF::app();
        $app->container()->set('zdessController', $routeMatch->getController());
        $app->container()->set('zdessAction', $routeMatch->getAction());
    }

    public static function templaterGlobalData(\XF\App $app, array &$data, $reply)
    {
        $data['reactionsActive'] = array_filter($data['reactionsActive'], function(array $reaction) use ($app, &$data)
        {
            return $reaction['reaction_id'] == 1 || !isset($reaction['zdess_user_criteria']) || $app->criteria('XF:User', $reaction['zdess_user_criteria'])->isMatched(\XF::visitor());
        });
    }

    public static function entityPreSave(Entity $entity)
    {
        if ($entity instanceof ProfilePost && $entity->message_state === 'deleted')
        {
            $entity->zdess_sticky = false;
        }
    }
}