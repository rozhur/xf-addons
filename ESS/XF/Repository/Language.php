<?php 

namespace ZD\ESS\XF\Repository;

class Language extends XFCP_Language
{
    public function getUserSelectableLanguages(\XF\Entity\User $user = null)
    {
        if (!$user)
        {
            $user = \XF::visitor();
        }

        $languages = [];
        foreach ($this->getLanguageTree(false)->getFlattened(0) AS $id => $record)
        {
            if ($user->hasAdminPermission('language') || $record['record']->user_selectable)
            {
                $languages[$id] = $record['record'];
            }
        }

        return $languages;
    }
}
