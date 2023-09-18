<?php

namespace ZD\ESS\Entity;

use XF\Mvc\Entity\Entity;

trait UserBehalfTrait
{
    public function handle(\Closure $closure)
    {
        $this->toggleUser(false);
        $result = $closure();
        $this->toggleUser(true);
        return $result;
    }

    public function toggleUser($revert = null)
    {
        /** @var Entity|UserBehalfInterface $this */
        if (!$this->zdess_real_user_id || !$this->getOption('toggle_user'))
        {
            return;
        }

        if ($revert === null)
        {
            $revert = $this['user_id'] == $this->zdess_real_user_id;
        }

        $this->set('user_id', $revert ? ($this->isChanged('user_id') ? $this->getPreviousValue('user_id') : $this->user_id) : $this->zdess_real_user_id);
    }
}