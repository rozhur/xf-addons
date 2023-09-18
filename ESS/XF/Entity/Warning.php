<?php 

namespace ZD\ESS\XF\Entity;

class Warning extends XFCP_Warning
{
    protected function _postDelete()
    {
        parent::_postDelete();

        $alertRepo = $this->repository('XF:UserAlert');
        $alertRepo->fastDeleteAlertsToUser(
            $this->user_id, 'warning', $this->warning_id, 'insert'
        );
    }

    public function canView(&$error = null)
    {
        return \XF::visitor()->user_id === $this->user_id || parent::canView($error);
    }
}
