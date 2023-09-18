<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;
use ZD\ESS\Entity\UserBehalfInterface;

class ConversationMessage extends XFCP_ConversationMessage implements UserBehalfInterface
{
    public function canEdit(&$error = null)
    {
        return parent::canEdit($error) && $this->canManipulate();
    }

    public function canCleanSpam()
    {
        return parent::canCleanSpam() && $this->canManipulate();
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

    public function canViewRealUser()
    {
        $visitor = \XF::visitor();

        return $visitor->user_id == $this->user_id || $visitor->hasPermission('conversation', 'viewRealUser');
    }

    public static function getStructure(Structure $structure)
    {
        return \ZD\ESS\Util\UserBehalf::getStructure(parent::getStructure($structure));
    }
}