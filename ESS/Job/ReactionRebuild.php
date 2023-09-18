<?php

namespace ZD\ESS\Job;

use XF\Entity\ReactionContent;
use XF\Job\AbstractJob;
use XF\Mvc\Entity\AbstractCollection;

class ReactionRebuild extends AbstractJob
{
    protected $defaultData = [
        'count' => 0,
        'total' => 0,
        'onlyPositiveNegative' => false
    ];

    public function run($maxRunTime)
    {
        $startTime = microtime(true);

        $reactionCountRepo = $this->app->repository('ZD\ESS:ReactionCount');

        $reactionCountFinder = $this->app->finder('XF:ReactionContent');

        /** @var ReactionContent[]|AbstractCollection $reactionContents */
        $reactionContents = $reactionCountFinder->fetch(500, $this->data['count']);

        $count = $reactionContents->count();
        if (!$count)
        {
            return $this->complete();
        }

        $onlyPositiveNegative = $this->data['onlyPositiveNegative'];

        if (!$this->data['total'])
        {
            $this->data['total'] = $reactionCountFinder->total();

            if (!$onlyPositiveNegative)
            {
                $this->app->db()->query('TRUNCATE TABLE zd_ess_reaction_count');
            }

            $this->app->db()->query('UPDATE xf_user SET zdess_reaction_score_positive = 0, zdess_reaction_score_negative = 0');
        }

        foreach ($reactionContents as $content)
        {
            if (!$onlyPositiveNegative)
            {
                $reactionCountRepo->updateReactionCount('received', '+', $content->reaction_id, $content->content_user_id);
                $reactionCountRepo->updateReactionCount('given', '+', $content->reaction_id, $content->reaction_user_id);
            }

            $reactionCountRepo->updateReactionPositiveNegative($content, '+');

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
        return sprintf('%s... %s/%s', \XF::phrase('zdess_rebuilding_reaction_count'), $this->data['count'], $this->data['total']);
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