<?php

namespace ZD\ESS\Repository;

use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Repository;
use XF\Entity\ReactionContent;

class ReactionCount extends Repository
{
    public function createReactionCount($reactionId, $userId)
    {
        /** @var \ZD\ESS\Entity\ReactionCount $reactionCount */
        $reactionCount = $this->em->create('ZD\ESS:ReactionCount');
        $reactionCount->reaction_id = $reactionId;
        $reactionCount->user_id = $userId;

        return $reactionCount;
    }

    public function updateReactionCount($field, $operator, $reactionId, $userId)
    {
        $this->db()->query("
            INSERT INTO zd_ess_reaction_count (reaction_id, user_id, $field)
            VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE $field = GREATEST(0, CAST($field AS SIGNED) $operator 1)
        ", [$reactionId, $userId]);
    }

    public function updateReactionPositiveNegative(ReactionContent $content, $operator)
    {
        $reaction = $content->Reaction;
        if ($reaction === null)
        {
            return;
        }

        $type = $reaction->getReactionType();
        switch ($type)
        {
            case 'positive':
            case 'negative':
                $score = $reaction->reaction_score;
                $score = $score < 0 ? -$score : $score;
                $user = $content->Owner;
                if (!$user)
                {
                    return;
                }

                $this->db()->query("
                        UPDATE xf_user SET zdess_reaction_score_$type = GREATEST(0, CAST(zdess_reaction_score_$type AS SIGNED) $operator $score)
                        WHERE user_id = ?;
                    ", [$user->user_id]);
        }
    }

    public function rebuildReactionCount()
    {
        $reactionContentFinder = $this->finder('XF:ReactionContent');

        /** @var ReactionContent[]|AbstractCollection $reactionContents */
        $reactionContents = $reactionContentFinder->fetch();

        $this->db()->beginTransaction();

        $this->db()->query('TRUNCATE TABLE zd_ess_reaction_count');
        $this->db()->query('UPDATE xf_user SET zdess_reaction_score_positive = 0, zdess_reaction_score_negative = 0');

        foreach ($reactionContents as $content)
        {
            $this->updateReactionCount('received', '+', $content->reaction_id, $content->content_user_id);
            $this->updateReactionCount('given', '+', $content->reaction_id, $content->reaction_user_id);

            $this->updateReactionPositiveNegative($content, '+');
        }

        $this->db()->commit();
    }
}
