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
class Thread extends XFCP_Thread implements CustomLink
{
    public function getCustomLinkIdentifierKey()
    {
        return 'thread_id';
    }

    public function getCustomLinkIdentifierValue()
    {
        return $this->thread_id;
    }

    public function canEditCustomLink()
    {
        if (!$this->canEdit())
        {
            return false;
        }

        return \XF::visitor()->hasNodePermission($this->node_id, 'editThreadCustomLink');
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
        return $this->zdir_custom_link;
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
        return \ZD\IR\Util\CustomLink::getFullStructure(parent::getStructure($structure));
    }
}