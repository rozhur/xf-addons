<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * @property array $zdess_user_criteria
 */
class Reaction extends XFCP_Reaction
{
    protected function verifyZdessUserCriteria(&$criteria)
    {
        $userCriteria = $this->app()->criteria('XF:User', $criteria);
        $criteria = $userCriteria->getCriteria();
        return true;
    }

    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isChanged('reaction_score') || $this->isChanged('is_custom_score'))
        {
            $this->app()->jobManager()->enqueueUnique('zdessReactionCountRebuild' . $this->reaction_id, 'ZD\ESS:ReactionRebuild', [
                'onlyPositiveNegative' => true
            ]);
        }
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_user_criteria'] = ['type' => self::JSON_ARRAY, 'default' => []];

        return $structure;
    }
}