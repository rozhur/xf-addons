<?php

namespace ZD\ESS\XF\Searcher;

use XF\Mvc\Entity\Finder;

class User extends XFCP_User
{
    protected function applySpecialCriteriaValue(Finder $finder, $key, $value, $column, $format, $relation)
    {
        $result = parent::applySpecialCriteriaValue($finder, $key, $value, $column, $format, $relation);

        if ($result)
        {
            return true;
        }


        if ($key == 'zdess_reactions')
        {
            if (!is_array($value))
            {
                $value = [$value];
            }

            $reactionFinder = $this->em->getFinder('ZD\ESS:ReactionCount');

            $where = [];
            foreach ($value as $reactionId => $parts)
            {
                if ($reactionId === 0 || !is_numeric($reactionId)) continue;

                $start = intval($parts['start'] ?? 1);
                $start = max($start, 0);

                $end = intval($parts['end'] ?? -1);
                $end = max($end, -1);

                if ($start === 0 && $end === -1) continue;

                $expression = '(reaction_id = ' . $finder->quote($reactionId, 'integer');

                if ($start != 0)
                {
                    $expression .= ' AND received >= ' . $finder->quote($start, 'integer');
                }

                if ($end != -1)
                {
                    $expression .= ' AND received <= ' . $finder->quote($end, 'integer');
                }

                $expression .= ')';

                $where[] = $expression;
            }

            if (!empty($where))
            {
                $reactionFinder->whereSql(implode(' OR ', $where));

                $ids = $reactionFinder->fetchColumns('user_id');
                if (empty($ids))
                {
                    $finder->whereImpossible();
                }
                else
                {
                    $ids = array_values($ids);
                    $ids = array_unique($ids, SORT_REGULAR);

                    $finder->whereIds($ids);
                }
            }

            return true;
        }

        return false;
    }
}