<?php 

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property bool $zdess_forum_open
 */
class Forum extends XFCP_Forum
{
    public function canCreateThread(&$error = null)
    {
        return parent::canCreateThread($error) && ($this->zdess_forum_open || $this->canLockUnlock());
    }

    public function canLockUnlock(&$error = null)
    {
        $visitor = \XF::visitor();
        return $visitor->user_id && $visitor->hasNodePermission($this->node_id, 'lockUnlockForum');
    }

    public function canViewModerators(&$error = null)
    {
        if (!$this->canView($error))
        {
            return false;
        }

        $visitor = \XF::visitor();
        return $visitor->user_id && $visitor->hasNodePermission($this->node_id, 'viewForumModerators');
    }

    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isUpdate() && $this->getOption('log_moderator'))
        {
            $this->app()->logger()->logModeratorChanges('forum', $this);
        }
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_forum_open'] = ['type' => self::BOOL, 'default' => true];

        $structure->options['log_moderator'] = true;

        return $structure;
    }
}
