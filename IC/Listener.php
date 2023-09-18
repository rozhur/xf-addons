<?php

namespace ZD\IC;

use XF\Entity\UserAlert;

class Listener
{
    public static function appSetup(\XF\App $app)
    {
        $app['emitter'] = function ()
        {
            return new \Emitter("127.0.0.1", 3999);
        };
    }

    public static function entityPostSave(\XF\Mvc\Entity\Entity $entity)
    {
        if ($entity instanceof UserAlert)
        {
            self::emitUnviewedAlerts($entity);
        }
    }

    public static function entityPostDelete(\XF\Mvc\Entity\Entity $entity)
    {
        if ($entity instanceof UserAlert)
        {
            self::emitUnviewedAlerts($entity);
        }
    }

    public static function emitUnviewedAlerts(UserAlert $entity)
    {
        /** @var XF\Repository\UserAlert $alertRepo */
        $alertRepo = $entity->repository('XF:UserAlert');
        $alertRepo->emitUnviewedAlerts($entity->alerted_user_id);
    }

    public static function emitMembersOnlineWidget()
    {
        self::emitter()->emit('update_widget', 'members_online');
    }

    /** @return \Emitter */
    public static function emitter()
    {
        return \XF::app()['emitter'];
    }
}