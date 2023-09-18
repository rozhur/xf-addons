<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;
use ZD\ESS\Entity\UserBehalfInterface;

class ProfilePostComment extends XFCP_ProfilePostComment implements UserBehalfInterface
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

        return $visitor->user_id == $this->user_id || $visitor->hasPermission('general', 'viewRealUser');
    }

    public static function getStructure(Structure $structure)
    {
        return \ZD\ESS\Util\UserBehalf::getStructure(parent::getStructure($structure));
    }
}