<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;
use ZD\ESS\Entity\UserBehalfInterface;
use ZD\ESS\Entity\UserBehalfTrait;

class Post extends XFCP_Post implements UserBehalfInterface
{
    use UserBehalfTrait;
    
    public function canEdit(&$error = null)
    {
        return $this->handle(function () use (&$error) { return parent::canEdit($error) && $this->canManipulate(); });
    }

    public function canDelete($type = 'soft', &$error = null)
    {
        return $this->handle(function () use ($type, &$error) { return parent::canDelete($type, $error) && $this->canManipulate(); });
    }

    public function canUndelete(&$error = null)
    {
        return $this->handle(function () use (&$error) { return parent::canUndelete($error) && $this->canManipulate(); });
    }

    public function canCleanSpam()
    {
        return $this->handle(function () { return parent::canCleanSpam() && $this->canManipulate(); });
    }

    public function canApproveUnapprove(&$error = null)
    {
        return $this->handle(function () use (&$error) { return parent::canApproveUnapprove($error) && $this->canManipulate(); });
    }

    public function canCopy(&$error = null)
    {
        return $this->handle(function () use (&$error) { return parent::canCopy($error) && $this->canManipulate(); });
    }

    public function canMerge(&$error = null)
    {
        return $this->handle(function () use (&$error) { return parent::canMerge($error) && $this->canManipulate(); });
    }

    public function canUseInlineModeration(&$error = null)
    {
        return $this->handle(function () use (&$error) { return parent::canUseInlineModeration($error) && $this->canManipulate(); });
    }

    public function canMove(&$error = null)
    {
        return $this->handle(function () use (&$error) { return parent::canMove($error) && $this->canManipulate(); });
    }

    public function canViewHistory(&$error = null)
    {
        return $this->handle(function () use (&$error) { return parent::canViewHistory($error) && $this->canManipulate(); });
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

            /** @var User $user */
            $user = $this->User;
            if (!$user)
            {
                return true;
            }
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

    public function canViewRealUser()
    {
        $visitor = \XF::visitor();

        return $visitor->user_id && $visitor->user_id == $this->zdess_real_user_id || $visitor->hasNodePermission($this->Thread->Forum->node_id, 'postBehalf');
    }

    protected function _preSave()
    {
        parent::_preSave();
        $this->toggleUser(true);
    }

    protected function _preDelete()
    {
        parent::_preDelete();
        $this->toggleUser(true);
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->relations['LastEditUser'] = [
            'entity' => 'XF:User',
            'type' => self::TO_ONE,
            'conditions' => 'last_edit_user_id',
            'primary' => true
        ];

        return \ZD\ESS\Util\UserBehalf::getStructure($structure);
    }
}