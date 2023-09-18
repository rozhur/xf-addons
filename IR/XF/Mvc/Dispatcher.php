<?php

namespace ZD\IR\XF\Mvc;

use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\Error;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\Reroute;
use XF\Mvc\RouteMatch;

class Dispatcher extends XFCP_Dispatcher
{
    public function route($routePath)
    {
        $match = parent::route($routePath);

        if (!\XF::$debugMode && !($this->app instanceof \XF\Api\App) && $this->app->options()['zdirRedirectRouteErrorsToIndex'] && $match->getAction() === 'DispatchError')
        {
            $match->setController('XF:Index');
            $match->setAction('index');
            $match->setParams([]);
        }

        return $match;
    }

    public function dispatchClass(
        $controllerClass, $action, RouteMatch $match, &$controller = null, AbstractReply $previousReply = null
    )
    {
        $reply = parent::dispatchClass($controllerClass, $action, $match, $controller, $previousReply);

        if (\XF::$debugMode || $this->app instanceof \XF\Api\App || !$this->app->options()['zdirRedirectRouteErrorsToIndex'])
        {
            return $reply;
        }

        if ($reply instanceof Reroute)
        {
            $params = $reply->getMatch()->getParams();
            if (isset($params['code']) && $params['code'] === 'invalid_action')
            {
                $match->setAction('index');
                $reply->setMatch($match);
            }
        }
        else if ($reply instanceof Error)
        {
            foreach ($reply->getErrors() as $error)
            {
                if (!($error instanceof \XF\Phrase))
                {
                    continue;
                }

                if ($error->getName() === 'do_not_have_permission' || $error->getName() === 'action_available_via_post_only' || $error->getName() === 'you_must_be_super_admin_to_access_this_page' || preg_match('/_not_found$/', $error->getName()))
                {
                    if ($reply->getAction() !== 'Index' && ($previousReply === null || $previousReply->getAction() !== 'Index'))
                    {
                        $reply = new Reroute($this->getRouter()->getNewRouteMatch($reply->getControllerClass(), 'index', $match->getParams(),  $match->getResponseType()));
                    }
                    else
                    {
                        $reply = new Redirect($this->getRouter()->buildLink('index'), 'permanent');
                    }
                }
                break;
            }
        }
        return $reply;
    }
}