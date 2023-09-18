<?php

namespace ZD\IR\XF\Mvc;

use XF\Http\Request;
use XF\Mvc\RouteMatch;
use ZD\IR\Entity\CustomLink;
use ZD\IR\XF\Entity\Thread;

class Router extends XFCP_Router
{
    protected function isPublic()
    {
        $app = \XF::app();
        return $app['router.public'] === $this && \XF::app();
    }

    public function routePreProcessRouteFilter(\XF\Mvc\Router $router, $path, RouteMatch $match, Request $request = null)
    {
        if (!$this->isPublic())
        {
            return parent::routePreProcessRouteFilter($router, $path, $match, $request);
        }

        if (!$this->routeFiltersIn)
        {
            return $match;
        }

        foreach ($this->routeFiltersIn AS $filter)
        {
            [$from, $to] = $this->routeFilterToRegex(
                urldecode($filter['replace_route']), urldecode($filter['find_route'])
            );

            $from = rtrim($from, '/#');

            $newRoutePath = preg_replace($from . '(\d+/|/)#', $to . '$1', $path);
            if ($newRoutePath != $path)
            {
                $match->setPathRewrite($newRoutePath);
                return $match;
            }
        }

        return $match;
    }

    public function routeToController($path, Request $request = null)
    {
        if (\XF::app()->options()['includeTitleInUrls'] || !$this->isPublic())
        {
            return parent::routeToController($path, $request);
        }

        $match = $this->getNewRouteMatch();
        $path = urldecode($path);

        if (strlen($path) && strpos($path, '/') === false)
        {
            $path .= '/';
        }

        foreach ($this->routePreProcessors as $preProcessor)
        {
            if (!is_callable($preProcessor))
            {
                continue;
            }

            /** @var RouteMatch $newMatch */
            $newMatch = call_user_func($preProcessor, $this, $path, $match, $request);
            if ($newMatch)
            {
                if ($newMatch->getPathRewrite() !== null)
                {
                    $path = $newMatch->getPathRewrite();
                    $newMatch->setPathRewrite(null);
                }
                $match = $newMatch;
            }
        }

        if ($path === '')
        {
            $path = 'index';
        }

        $parts = explode('/', $path, 2);
        $prefix = $parts[0];
        $suffix = $parts[1] ?? '';

        $matched = false;

        $customLinks = \XF::app()->container('zdirCustomLinksIn');

        $entity = $customLinks[$prefix] ?? false;

        if ($entity)
        {
            $id = $entity['id'];
            $match->setParam($id['key'], $id['value']);
            $possibleRoutes = $this->routes[$entity['route']];

            if (is_numeric($id['value']))
            {
                $suffix = $id['value'] . ($suffix ? '/' . $suffix : '');
            }
        }
        else
        {
            $parts = preg_split('#(?<=[a-z])(?=\d+(/|$))#', $path);
            if (sizeof($parts) > 1)
            {
                $parts = explode('/', implode('/', $parts), 2);
                $prefix = $parts[0];
                $suffix = $parts[1];
            }

            if (!isset($this->routes[$prefix]))
            {
                return $match;
            }

            $possibleRoutes = $this->routes[$prefix];
        }

        foreach ($possibleRoutes AS $route)
        {
            $newMatch = $this->suffixMatchesRoute($suffix, $route, $match, $request);
            if ($newMatch)
            {
                $match = $newMatch;
                $matched = true;
                break;
            }
        }

        if (!$matched && isset($possibleRoutes['']))
        {
            $route = $possibleRoutes[''];

            $match->setController($route['controller']);
            if (!empty($route['force_action']))
            {
                $match->setAction($route['force_action']);
            }
            else
            {
                $match->setAction(strlen($suffix) ? $suffix : $this->defaultAction);
            }

            if (isset($route['context']))
            {
                $match->setSectionContext($route['context']);
            }
        }

        return $match;
    }

    protected function buildRouteUrl($prefix, array $route, $action, $data = null, array &$parameters = [])
    {
        $routeUrl = parent::buildRouteUrl($prefix, $route, $action, $data, $parameters);

        if (is_string($routeUrl))
        {
            if (!\XF::app()->options()['includeTitleInUrls'] && $this->isPublic())
            {
                if ($data instanceof CustomLink)
                {
                    $id = $data->getCustomLinkIdentifierValue();
                }
                else if (is_array($data))
                {
                    $id = reset($data);
                }
                else
                {
                    $id = 0;
                }

                $customLinks = \XF::app()->container('zdirCustomLinksOut');
                $routeUrl = self::buildCustomLink($customLinks, $prefix, $id, $routeUrl);
            }

            $formatParts = explode('/', trim($route['format'], '/'), 2);
            $urlParts = explode('/', trim($routeUrl, '/'));

            $lastUrlPart = end($urlParts);

            if (!isset($formatParts[0]) || $lastUrlPart !== $formatParts[0] && substr($routeUrl, -1) == '/')
            {
                $routeUrl = substr($routeUrl, 0, strlen($routeUrl) - 1);
            }
        }

        return $routeUrl;
    }

    protected static function buildCustomLink(array $customLinks, $prefix, $id, $routeUrl)
    {
        if (isset($customLinks[$prefix][$id]))
        {
            $routeUrl = preg_replace('#^[^/]*/(([^/]*\.)?\d+|[^/]*)#', $customLinks[$prefix][$id], $routeUrl);
        }
        else
        {
            $routeUrl = preg_replace('#([a-z]+)/(\d+(/.*|$))#i', '$1$2', $routeUrl);
        }
        return $routeUrl;
    }
}