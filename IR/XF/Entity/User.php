<?php

namespace ZD\IR\XF\Entity;

use XF\Mvc\Entity\Structure;
use ZD\IR\Entity\CustomLink;

/**
 * COLUMNS
 * @property string $zdir_custom_link
 *
 * GETTERS
 * @property array $custom_link_length_limits
 */
class User extends XFCP_User implements CustomLink
{
    public function getCustomLinkIdentifierKey()
    {
        return 'user_id';
    }

    public function getCustomLinkIdentifierValue()
    {
        return $this->user_id;
    }

    public function canEditCustomLink()
    {
        return $this->canEditProfile() && \XF::visitor()->hasPermission('general', 'editCustomLink');
    }

    public function canBypassCustomLinkRegex()
    {
        return $this->hasPermission('general', 'bypassCustomLinkRegex');
    }

    public function canBypassCustomLinkLength()
    {
        return $this->hasPermission('general', 'bypassCustomLinkLength');
    }

    public function getCustomLinkLengthLimits()
    {
        /** @var User $visitor */
        $visitor = \XF::visitor();

        if ($visitor->canBypassCustomLinkLength())
        {
            return [
                'min' => 0,
                'max' => $this->getMaxLength('zdir_custom_link')
            ];
        }

        return $this->app()->options()->zdirCustomLinkLength;
    }

    public function getCustomLink()
    {
        return $this->getValue('zdir_custom_link');
    }

    public function isCustomLinkChanged()
    {
        return $this->isChanged('zdir_custom_link');
    }

    protected function verifyZdirCustomLink(&$zdirCustomLink)
    {
        return \ZD\IR\Util\CustomLink::verifyCustomLink($this, $zdirCustomLink);
    }

    protected function _postSave()
    {
        if ($this->isCustomLinkChanged())
        {
            $this->repository('ZD\IR:CustomLink')->rebuildCustomLinkCache(null, true);
        }

        parent::_postSave();
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        return \ZD\IR\Util\CustomLink::getFullStructure($structure);
    }
}