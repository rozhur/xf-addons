<?php

namespace ZD\IR\XF\Entity;

use XF\Mvc\Entity\Structure;
use ZD\IR\Entity\CustomLink;

class Forum extends XFCP_Forum implements CustomLink
{
    public function getCustomLinkIdentifierKey()
    {
        return 'node_id';
    }

    public function getCustomLinkIdentifierValue()
    {
        return $this->node_id;
    }

    public function getCustomLink()
    {
        return $this->node_name;
    }

    public function isCustomLinkChanged()
    {
        return $this->Node->isChanged('node_name');
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
        return \ZD\IR\Util\CustomLink::getCustomLinkIdentifierStructure(parent::getStructure($structure));
    }
}