<?php

namespace ZD\ESS\Job;

use XF\Job\AbstractJob;

class ReactionReset extends AbstractJob
{
    protected $defaultData = [
        'reactionId' => null,
        'count' => 0,
        'total' => 0
    ];

    public function run($maxRunTime)
    {
        $startTime = microtime(true);

        if (!$this->data['reactionId'])
        {
            return $this->complete();
        }

        $reactionFinder = $this->app->finder('XF:ReactionContent')->where('reaction_id', $this->data['reactionId']);

        $total = $reactionFinder->total();
        if (!$total)
        {
            return $this->complete();
        }

        if (!$this->data['total'])
        {
            $this->data['total'] = $total;
        }

        foreach ($reactionFinder->fetch(500) AS $reaction)
        {
            $reaction->delete(false);

            $this->data['count']++;

            if ($maxRunTime && microtime(true) - $startTime > $maxRunTime)
            {
                break;
            }
        }

        return $this->resume();
    }

    public function getStatusMessage()
    {
        return sprintf('%s... %s/%s', \XF::phrase('removing_reactions'), $this->data['count'], $this->data['total']);
    }

    public function canCancel()
    {
        return true;
    }

    public function canTriggerByChoice()
    {
        return false;
    }
}