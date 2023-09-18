<?php 

namespace ZD\ESS\XF\Repository;

class Style extends XFCP_Style
{
    public function getUserSelectableStyles(\XF\Entity\User $user = null)
    {
        if (!$user)
        {
            $user = \XF::visitor();
        }

        $styles = [];
        foreach ($this->getStyleTree(false)->getFlattened(0) AS $id => $record)
        {
            if ($user->hasAdminPermission('style') || $record['record']->user_selectable)
            {
                $styles[$id] = $record['record'];
            }
        }
        return $styles;
    }
}
