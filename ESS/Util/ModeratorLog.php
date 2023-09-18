<?php

namespace ZD\ESS\Util;

class ModeratorLog
{
    public static function isLoggable(\XF\Mvc\Entity\Entity $content, \XF\Entity\User $actor)
    {
        switch (\XF::options()['zdessLoggingModeratorActions'])
        {
            case 'always':
                return true;
            case 'always_except_own_content':
                return empty($content['user_id']) || $content['user_id'] != $actor->user_id;
            default:
                return false;
        }
    }
    public static function isLoggableUser()
    {
        return \XF::options()['zdessLoggingModeratorActions'] != 'default';
    }
}