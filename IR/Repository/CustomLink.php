<?php

namespace ZD\IR\Repository;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;

class CustomLink extends Repository
{
    public function applyCustomLinkCache(&$data, $entitiesWithLinks, $route, \Closure $closure = null)
    {
        /** @var Entity|\ZD\IR\Entity\CustomLink $entity */
        foreach ($entitiesWithLinks AS $entity)
        {
            $link = str_replace('/', '', $entity->getCustomLink());
            $link = mb_strtolower($link);

            $key = $entity->getCustomLinkIdentifierKey();
            $value = $entity->getCustomLinkIdentifierValue();

            if (isset($data['in'][$link]))
            {
                continue;
            }

            $data['in'][$link] = [
                'route' => $route,
                'id' => ['key' => $key, 'value' => $value]
            ];

            $data['out'][$route][$value] = $link;

            if ($closure != null)
            {
                if ($closure() === false)
                {
                    break;
                }
            }
        }
    }

    public function rebuildCustomLinkCacheOnce()
    {
        \XF::runOnce('zdirCustomLinkCacheRebuild', function () {
            $this->rebuildCustomLinkCache();
        });
    }

    public function rebuildCustomLinkCache($data = null, $updateContainer = false)
    {
        if ($data === null)
        {
            $data = $this->getCustomLinkCacheData();
        }

        $in = $data['in'] ?? [];
        $out = $data['out'] ?? [];

        \XF::registry()->set('zdirCustomLinksIn', $in);
        \XF::registry()->set('zdirCustomLinksOut', $out);

        if ($updateContainer)
        {
            $container = \XF::app()->container();

            $container['zdirCustomLinksIn'] = $in;
            $container['zdirCustomLinksOut'] = $out;
        }

        return $data;
    }

    protected function getCustomLinkCacheData()
    {
        $cache = [];

        foreach (\ZD\IR\Util\CustomLink::ENTITIES as $data)
        {
            $this->fetchCustomLinkCacheData($cache, $data['entity'], $data['route'], $data['linkKey'] ?? 'zdir_custom_link', $data['with'] ?? null);
        }

        return $cache;
    }

    protected function fetchCustomLinkCacheData(&$cache, $type, $route, $linkKey = 'zdir_custom_link', $with = null)
    {
        $where = $with ? $with . '.' . $linkKey : $linkKey;
        $entitiesWithLinks = $this->finder($type)
            ->where([[$where, '!=', null], [$where, '!=', '']])
            ->fetch();

        $this->applyCustomLinkCache($cache, $entitiesWithLinks, $route);
    }
}