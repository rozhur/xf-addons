<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property bool $zdess_disallow_open_discussion
 */
class Thread extends XFCP_Thread
{
    public function canEdit(&$error = null)
    {
        return parent::canEdit($error) && $this->canManipulate();
    }

    public function canDelete($type = 'soft', &$error = null)
    {
        return parent::canDelete($type, $error) && $this->canManipulate();
    }

    public function canUndelete(&$error = null)
    {
        return parent::canUndelete($error) && $this->canManipulate();
    }

    public function canCleanSpam()
    {
        return parent::canCleanSpam() && $this->canManipulate();
    }

    public function canApproveUnapprove(&$error = null)
    {
        return parent::canApproveUnapprove($error) && $this->canManipulate();
    }

    public function canCopy(&$error = null)
    {
        return parent::canCopy($error) && $this->canManipulate();
    }

    public function canMerge(&$error = null)
    {
        return parent::canMerge($error) && $this->canManipulate();
    }

    public function canMove(&$error = null)
    {
        return parent::canMove($error) && $this->canManipulate();
    }

    public function canChangeType(&$error = null): bool
    {
        return parent::canChangeType($error) && $this->canManipulate();
    }

    public function canCreatePoll(&$error = null)
    {
        return parent::canCreatePoll($error) && $this->canManipulate();
    }

    public function canLockUnlockAny(&$error = null)
    {
        return parent::canLockUnlock($error);
    }

    public function canLockUnlock(&$error = null)
    {
        $visitor = \XF::visitor();
        return parent::canLockUnlock($error) || $visitor->user_id && ($this->isInsert() || $visitor->user_id === $this->user_id) && !$this->zdess_disallow_open_discussion && $visitor->hasNodePermission($this->node_id, 'lockUnlockOwnThread');
    }

    public function canManipulate()
    {
        $visitor = \XF::visitor();
        if (
            $visitor->user_id !== $this->user_id && (
                !$visitor->is_super_admin && $this->User->is_super_admin ||
                !$visitor->is_admin && $this->User->is_admin ||
                !$visitor->is_super_moderator && $this->User->is_super_moderator ||
                !$visitor->is_moderator && $this->User->is_moderator
            )
        )
        {
            return false;
        }

        return true;
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_disallow_open_discussion'] = ['type' => self::BOOL, 'default' => false];

        return $structure;
    }
}