<?php

namespace ZD\ESS\XF\Entity;

use XF\Entity\Moderator;
use XF\Entity\Thread;
use XF\Mvc\Entity\Structure;
use ZD\ESS\Entity\ReactionCount;

/**
 * COLUMNS
 * @property bool $zdess_from_mobile
 * @property array $zdess_behalf_criteria
 * @property int $zdess_reaction_score_positive
 * @property int $zdess_reaction_score_negative
 *
 * GETTERS
 * @property array $available_behalf_users
 * @property bool $has_viewable_username_history
 * @property array $custom_title_length_limits
 * @property bool $is_super_moderator
 * @property int $is_from_mobile
 * @property ReactionCount[] $reaction_count
 *
 */
class User extends XFCP_User
{
    public function canEdit()
    {
        return parent::canEdit() && $this->canManipulate();
    }

    public function canManipulate()
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();
        if ($visitor->is_super_admin)
        {
            return true;
        }
        else
        {
            if (!$this->user_id || $this->user_id === $visitor->user_id)
            {
                return true;
            }

            $user = $this;
            if ($user->is_super_admin)
            {
                return false;
            }
            else if ($user->is_admin)
            {
                return $visitor->is_admin;
            }
            else if ($user->is_super_moderator)
            {
                return $visitor->is_super_moderator;
            }
            else if ($user->is_moderator)
            {
                return $visitor->is_moderator;
            }
            else
            {
                return true;
            }
        }
    }

    public function hasViewableUsernameHistory(): bool
    {
        if ($this->username_date == 0)
        {
            return false;
        }

        if ($this->canViewFullUsernameHistory())
        {
            return true;
        }

        if (!$this->has_viewable_username_history)
        {
            return false;
        }

        $cutOff = \XF::$time - 86400 * $this->app()->options()->usernameChangeRecentLimit;
        return ($this->username_date_visible >= $cutOff);
    }

    public function canViewFullUsernameHistory(&$error = null): bool
    {
        $visitor = \XF::visitor();

        return $visitor->canBypassUserPrivacy() && $this->username_date > 0;
    }

    public function canDeleteUsernameHistory()
    {
        $visitor = \XF::visitor();

        return $visitor->hasPermission('general',
            $this->user_id === $visitor->user_id ? 'deleteUsernameHistory' : 'deleteAnyUsernameHistory');
    }

    public function canHardDeleteUsernameHistory()
    {
        return $this->hasPermission('general', 'hardDeleteUsernameHistory');
    }

    public function canViewFullProfile(&$error = null)
    {
        $result = parent::canViewFullProfile($error);
        if ($error instanceof \XF\Phrase && $this->is_banned)
        {
            $error = \XF::phrase('user_is_banned');
        }
        return $result;
    }

    public function canViewRegisterDate()
    {
        $visitor = \XF::visitor();

        if ($visitor->user_id == $this->user_id || $visitor->canBypassUserPrivacy())
        {
            return true;
        }

        /** @var UserOption $userOption */
        $userOption = $this->Option;

        return $userOption->zdess_show_reg_date;
    }

    public function canPostAnonymous(Thread $thread)
    {
        return $this->hasNodePermission($thread->Forum->node_id, 'postReplyAnonymous');
    }

    public function canBypassCustomTitleRegex()
    {
        return $this->hasPermission('general', 'bypassCustomTitleRegex');
    }

    public function canBypassCustomTitleLength()
    {
        return $this->hasPermission('general', 'bypassCustomTitleLength');
    }

    public function canChooseSuperUserGroup()
    {
        return $this->hasAdminPermission('userGroup');
    }

    public function canChangeStyle(&$error = null)
    {
        $styles = array_filter($this->app()->container('style.cache'), function($style)
        {
            return ($this->hasAdminPermission('style') || $style['user_selectable']);
        });
        return count($styles) > 1;
    }

    public function canChangeLanguage(&$error = null)
    {
        $languages = array_filter($this->app()->container('language.cache'), function($language)
        {
            return ($this->hasAdminPermission('language') || $language['user_selectable']);
        });
        return count($languages) > 1;
    }

    public function canBehalf($criteria = null)
    {
        if ($criteria === null)
        {
            $criteria = $this->zdess_behalf_criteria;
        }

        $visitor = \XF::visitor();
        return isset($criteria['user_groups']) && $visitor->isMemberOf($criteria['user_groups']) && (!isset($criteria['not_user_groups']) || !$visitor->isMemberOf($criteria['not_user_groups']));
    }

    public function getAvailableBehalfUsers()
    {
        $users = $this->db()->query("
            SELECT user_id, username, zdess_behalf_criteria
            FROM xf_user
            WHERE user_id != ? AND zdess_behalf_criteria != '' AND zdess_behalf_criteria != '[]'
        ", [\XF::visitor()->user_id])->fetchAllKeyed('user_id');

        return array_filter($users, function ($user)
        {
            $criteria = json_decode($user['zdess_behalf_criteria'], true);
            return $this->canBehalf($criteria);
        });
    }

    public function getHasViewableUsernameHistory()
    {
        /** @var \XF\Repository\UsernameChange $usernameChangeRepo */
        $usernameChangeRepo = $this->repository('XF:UsernameChange');
        $historyFinder = $usernameChangeRepo->findUsernameChangeHistoryForUser($this->user_id)->visibleOnly();

        return $historyFinder->limit(1)->total() > 0;
    }

    public function getCustomTitleLengthLimits()
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();

        if ($visitor->canBypassCustomTitleLength())
        {
            return [
                'min' => 0,
                'max' => $this->getMaxLength('custom_title')
            ];
        }

        return $this->app()->options()->zdirCustomTitleLength;
    }

    public function getIsSuperModerator()
    {
        /** @var Moderator $moderator */
        $moderator = $this->em()->find('XF:Moderator', $this->user_id);

        return $moderator && $moderator->is_super_moderator;
    }

    public function getIsFromMobile()
    {
        /** @var SessionActivity $activity */
        $activity = $this->Activity;
        return ($activity && $activity->zdess_from_mobile ? $activity->zdess_from_mobile : $this->zdess_from_mobile);
    }

    public function getReactionCount()
    {
        $reactionCount = $this->finder('ZD\ESS:ReactionCount')
            ->where('user_id', $this->user_id)
            ->keyedBy('reaction_id')
            ->fetch();

        /** @var Reaction[] $reactionList */
        $reactionList = $this->repository('XF:Reaction')->findReactionsForList(true);

        $reactions = [];
        foreach ($reactionList as $reaction)
        {
            $reactions[$reaction->reaction_id] = $reactionCount[$reaction->reaction_id] ?? $this->repository('ZD\ESS:ReactionCount')->createReactionCount($reaction->reaction_id, $this->user_id);
        }

        return $reactions;
    }

    public function getFollowing($limit = null)
    {
        /** @var \XF\Repository\UserFollow $userFollowRepo */
        $userFollowRepo = $this->repository('XF:UserFollow');

        $following = [];
        $followingCount = 0;
        if ($this->Profile->following)
        {
            $userFollowingFinder = $userFollowRepo->findFollowingForProfile($this);

            if ($limit !== null)
            {
                $userFollowingFinder->order($userFollowingFinder->expression('RAND()'));
                $following = $userFollowingFinder->fetch($limit)->pluckNamed('FollowUser');
            }

            $followingCount = $this->getOption('followingCount');
            if ($followingCount === null)
            {
                $followingCount = $userFollowingFinder->total();
                $this->setOption('followingCount', $followingCount);
            }
        }

        return [
            'following' => $following,
            'total' => $followingCount
        ];
    }

    public function getFollowers($limit = null)
    {
        /** @var \XF\Repository\UserFollow $userFollowRepo */
        $userFollowRepo = $this->repository('XF:UserFollow');
        $userFollowersFinder = $userFollowRepo->findFollowersForProfile($this);

        $followers = [];
        if ($limit !== null)
        {
            $userFollowersFinder->order($userFollowersFinder->expression('RAND()'));
            $followers = $userFollowersFinder->fetch($limit)->pluckNamed('User');
        }

        $followersCount = $this->getOption('followersCount');
        if ($followersCount === null)
        {
            $followersCount = $userFollowersFinder->total();
            $this->setOption('followersCount', $followersCount);
        }

        return [
            'followers' => $followers,
            'total' => $followersCount
        ];
    }

    public function verifyCustomTitle(&$title)
    {
        if (empty($title) || $title === $this->getExistingValue('custom_title'))
        {
            return true;
        }

        /** @var User $visitor */
        $visitor = \XF::visitor();
        if (!$this->getOption('admin_edit'))
        {
            if (!$visitor->canBypassCustomTitleLength())
            {
                $limit = $this->app()->options()->zdessCustomTitleLength;
                $length = strlen($title);

                if ($length < $limit['min'])
                {
                    $this->error(\XF::phrase('zdess_please_enter_custom_title_that_is_at_least_x_characters_long', [
                        'count' => $limit['min']
                    ]), 'custom_title');
                    return false;
                }
                else if ($length > $limit['max'])
                {
                    $this->error(\XF::phrase('zdess_please_enter_custom_title_that_is_at_most_x_characters_long', [
                        'count' => $limit['max']
                    ]), 'custom_title');
                    return false;
                }
            }

            if (!$visitor->canBypassCustomTitleRegex())
            {
                $regex = $this->app()->options()->zdessCustomTitleRegex;
                if (!empty($regex) && !preg_match($regex, $title))
                {
                    $this->error(\XF::phrase('zdess_please_enter_another_custom_title_required_format'), 'custom_title');
                    return false;
                }
            }
        }

        if ($visitor->is_moderator || $visitor->is_admin)
        {
            return true;
        }

        return parent::verifyCustomTitle($title);
    }

    protected function verifyUserGroupId(&$userGroupId)
    {
        if (!$userGroupId)
        {
            $userGroupId = $this->user_group_id ?: self::GROUP_REG;
            return true;
        }

        /** @var User $visitor */
        $visitor = \XF::visitor();
        if (!$visitor->canChooseSuperUserGroup() && $userGroupId != $this->user_group_id)
        {
            /** @var UserGroup $userGroup */
            $userGroup = $this->em()->find('XF:UserGroup', $userGroupId);
            if ($userGroup && $userGroup->zdess_super_user_group)
            {
                $userGroupId = $this->user_group_id ?: self::GROUP_REG;
            }
        }

        return true;
    }

    protected function verifySecondaryGroupIds(&$secondaryGroupIds)
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();

        if (!$visitor->canChooseSuperUserGroup())
        {
            /** @var UserGroup $userGroup */
            foreach ($this->finder('XF:UserGroup')->whereIds($secondaryGroupIds) as $userGroup)
            {
                if ($userGroup->zdess_super_user_group)
                {
                    unset($secondaryGroupIds[$userGroup->user_group_id]);
                }
            }

            /** @var UserGroup $userGroup */
            foreach ($this->finder('XF:UserGroup')->whereIds($this->secondary_group_ids) as $userGroup)
            {
                if ($userGroup->zdess_super_user_group)
                {
                    $secondaryGroupIds[] = $userGroup->user_group_id;
                }
            }
        }
        return true;
    }

    protected function verifyStyleId(&$styleId)
    {
        parent::verifyStyleId($styleId);

        if ($styleId && !$this->hasAdminPermission('style'))
        {
            $style = $this->_em->find('XF:Style', $styleId);
            if (!$style || !$style->user_selectable)
            {
                $styleId = $this->zdess_admin_style_id;
            }
        }

        return true;
    }

    protected function verifyLanguageId(&$languageId)
    {
        parent::verifyLanguageId($languageId);

        if ($languageId && !$this->hasAdminPermission('language'))
        {
            $language = $this->_em->find('XF:Language', $languageId);
            if (!$language || !$language->user_selectable)
            {
                $languageId = $this->language_id;
            }
        }

        return true;
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_from_mobile'] = ['type' => self::BOOL, 'changeLog' => false, 'default' => false];
        $structure->columns['zdess_behalf_criteria'] = ['type' => self::JSON_ARRAY, 'default' => []];
        $structure->columns['zdess_reaction_score_positive'] = ['type' => self::UINT, 'default' => 0, 'changeLog' => false];
        $structure->columns['zdess_reaction_score_negative'] = ['type' => self::UINT, 'default' => 0, 'changeLog' => false];

        $structure->getters['available_behalf_users'] = true;
        $structure->getters['has_viewable_username_history'] = true;
        $structure->getters['custom_title_length_limits'] = true;
        $structure->getters['is_super_moderator'] = true;
        $structure->getters['is_from_mobile'] = false;
        $structure->getters['reaction_count'] = true;
        $structure->getters['followers'] = false;
        $structure->getters['following'] = false;

        $structure->options['followersCount'] = null;
        $structure->options['followingCount'] = null;

        return $structure;
    }
}