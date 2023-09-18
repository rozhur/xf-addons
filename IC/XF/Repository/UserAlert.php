<?php

namespace ZD\IC\XF\Repository;

use XF\Mvc\Entity\Finder;

class UserAlert extends XFCP_UserAlert
{
    protected function deleteAlertsInternal(Finder $matches)
    {
        $userIds = $matches->fetchColumns('alerted_user_id');

        parent::deleteAlertsInternal($matches);

        $db = $this->db();
        $db->beginTransaction();

        $this->emitUnviewedAlerts($userIds);

        $db->commit();
    }

    public function emitUnviewedAlerts($userIds)
    {

        if (is_array($userIds))
        {
            foreach ($userIds as $userId)
            {
                $this->emitUnviewedAlerts($userId);
            }

            return;
        }

        $db = $this->db();

        $alertsUnviewed = $db->fetchOne('
                SELECT alerts_unviewed
                FROM xf_user
                WHERE user_id = ?
            ', $userIds);

        $this->emitter()
            ->to($userIds)
            ->emit('update_unviewed_alerts', $alertsUnviewed);
    }

    /** @return \Emitter */
    public function emitter()
    {
        return $this->app()['emitter'];
    }
}