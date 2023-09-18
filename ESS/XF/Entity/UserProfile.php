<?php

namespace ZD\ESS\XF\Entity;

use ZD\ESS\XF\Language;

class UserProfile extends XFCP_UserProfile
{
    public function getAge($bypassPrivacy = false)
    {
        $visitor = \XF::visitor();
        return parent::getAge($visitor->user_id == $this->user_id || $visitor->canBypassUserPrivacy());
    }

    public function getBirthday($bypassPrivacy = false)
    {
        $visitor = \XF::visitor();
        $result = parent::getBirthday($visitor->user_id == $this->user_id || $visitor->canBypassUserPrivacy());

        if (isset($result['format']) && $result['format'] === 'monthDay')
        {
            /** @var Language $language */
            $language = $this->app()->templater()->getLanguage();
            $result['format'] = $language->getDateFormatWithoutYear();
        }

        return $result;
    }
}