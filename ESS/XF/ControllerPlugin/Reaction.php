<?php

namespace ZD\ESS\XF\ControllerPlugin;

use XF\Mvc\Entity\Entity;

class Reaction extends XFCP_Reaction
{
    protected function validateReactionAction(Entity $content, &$existingReaction = null)
    {
        /** @var \ZD\ESS\XF\Entity\Reaction $reaction */
        $reaction = parent::validateReactionAction($content, $existingReaction);
        if ($reaction->isDefaultReaction())
        {
            return $reaction;
        }

        $userCriteria = $this->app->criteria('XF:User', $reaction->zdess_user_criteria);

        $visitor = \XF::visitor();
        if (!$userCriteria->isMatched($visitor))
        {
            throw $this->exception($this->noPermission());
        }

        return $reaction;
    }
}