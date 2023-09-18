<?php

namespace ZD\IR\XF\Entity;

use XF\Entity\AbstractNode;
use XF\Mvc\Entity\Structure;
use ZD\IR\Entity\CustomLink;

class Node extends XFCP_Node implements CustomLink
{
    public function getCustomLinkIdentifierKey()
    {
        $data = $this->Data;
        return $data instanceof CustomLink ? $data->getCustomLinkIdentifierKey() : 'node_id';
    }

    public function getCustomLinkIdentifierValue()
    {
        $data = $this->Data;
        return $data instanceof CustomLink ? $data->getCustomLinkIdentifierValue() : $this->node_id;
    }

    public function getCustomLink()
    {
        return $this->node_name;
    }

    public function isCustomLinkChanged()
    {
        return $this->isChanged('node_name');
    }

    protected function verifyNodeName(&$name)
    {
        $result = parent::verifyNodeName($name);

        $data = $this->Data;
        if ($data instanceof CustomLink && $result && $name !== '' && $name !== $this->node_name && \ZD\IR\Util\CustomLink::isCustomLinkExists($this, $name))
        {
            $this->error(\XF::phrase('node_names_must_be_unique'), 'Node.node_name');
            return false;
        }

        return $result;
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