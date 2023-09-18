<?php

namespace ZD\ESS\XF\Entity;

use ZD\ESS\Repository\ReactionCount;

class ReactionContent extends XFCP_ReactionContent
{
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isInsert() && $this->is_counted)
        {
            $this->updateReactionCount('+');
        }
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        if (!$this->isInsert() && $this->is_counted)
        {
            $this->updateReactionCount('-');
        }
    }

    protected function updateReactionCount($operator)
    {
        /** @var ReactionCount $repo */
        $repo = $this->repository('ZD\ESS:ReactionCount');

        $repo->updateReactionCount('received', $operator, $this->reaction_id, $this->content_user_id);
        $repo->updateReactionCount('given', $operator, $this->reaction_id, $this->reaction_user_id);

        $repo->updateReactionPositiveNegative($this, $operator);
    }
}