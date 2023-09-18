<?php

namespace ZD\ESS\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;
use ZD\ESS\XF\Entity\Reaction;
use ZD\ESS\XF\Entity\User;

/**
 * COLUMNS
 * @property int $reaction_count_id
 * @property int $reaction_id
 * @property int $user_id
 * @property int $received
 * @property int $given
 *
 * RELATIONS
 * @property Reaction $Reaction
 * @property User $User
 */
class ReactionCount extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'zd_ess_reaction_count';
        $structure->shortName = 'ZD\ESS:ReactionCount';
        $structure->primaryKey = 'reaction_count_id';
        $structure->columns = [
            'reaction_count_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'reaction_id' => ['type' => self::UINT],
            'user_id' => ['type' => self::UINT],
            'received' => ['type' => self::UINT, 'default' => 0],
            'given' => ['type' => self::UINT, 'default' => 0]
        ];

        $structure->relations = [
            'Reaction' => [
                'entity' => 'XF:Reaction',
                'type' => self::TO_ONE,
                'conditions' => 'reaction_id'
            ],
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id'
            ],
        ];

        return $structure;
    }
}