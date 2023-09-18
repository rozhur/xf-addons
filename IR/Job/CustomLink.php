<?php

namespace ZD\IR\Job;

use XF\Job\AbstractJob;

class CustomLink extends AbstractJob
{
    protected $defaultData = [
        'start' => 0,
        'steps' => 0,
        'batch' => 500,
        'entities' => \ZD\IR\Util\CustomLink::ENTITIES,
        'current' => '',
        'cache' => []
    ];

    public function run($maxRunTime)
    {
        $this->data['current'] = reset($this->data['entities']);

        $with = $this->data['current']['with'] ?? null;
        $linkKey = $this->data['current']['linkKey'] ?? 'zdir_custom_link';

        $startTime = microtime(true);

        $where = $with ? $with . '.' . $linkKey : $linkKey;

        $entitiesWithLinks = $this->app->finder($this->data['current']['entity'])
            ->where([[$where, '!=', null], [$where, '!=', '']])
            ->limit($this->data['batch'], $this->data['batch'] * $this->data['steps'])
            ->fetch()
            ->toArray();

        $this->data['steps']++;

        if (!$entitiesWithLinks)
        {
            $this->data['entities'] = array_slice($this->data['entities'], 1);
            if (empty($this->data['entities']))
            {
                $this->repository()->rebuildCustomLinkCache($this->data['cache']);
                return $this->complete();
            }

            $this->data['steps'] = $this->defaultData['steps'];
            $this->data['batch'] = $this->defaultData['batch'];

            return $this->resume();
        }

        $done = 0;

        $this->repository()->applyCustomLinkCache($this->data['cache'], $entitiesWithLinks, $this->data['current']['route'], function () use (&$done, $startTime, $maxRunTime)
        {
            $done++;
            $this->data['start']++;

            return !(microtime(true) - $startTime >= $maxRunTime);
        });

        $this->data['batch'] = $this->calculateOptimalBatch($this->data['batch'], $done, $startTime, $maxRunTime, 1000);

        return $this->resume();
    }

    public function getStatusMessage()
    {
        $actionPhrase = \XF::phrase('rebuilding');
        $typePhrase = \XF::phrase('zdir_custom_link');
        $entities = \ZD\IR\Util\CustomLink::ENTITIES;
        return sprintf('%s... %s: %s (%s)', $actionPhrase, $typePhrase, $this->data['current']['entity'] ?? reset($entities)['entity'], $this->data['start']);
    }

    public function canCancel()
    {
        return false;
    }

    public function canTriggerByChoice()
    {
        return true;
    }

    /** @return \ZD\IR\Repository\CustomLink */
    protected function repository()
    {
        return $this->app->repository('ZD\IR:CustomLink');
    }
}