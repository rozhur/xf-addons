<?php

namespace ZD\ESS\Entity;

use ZD\ESS\XF\Entity\User;

/**
 * COLUMNS
 * @property int $zdess_real_user_id
 *
 * RELATIONS
 * @property User $RealUser
 */
interface UserBehalfInterface
{
    public function canViewRealUser();
}