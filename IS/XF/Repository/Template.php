<?php 

namespace ZD\IS\XF\Repository;

class Template extends XFCP_Template
{
    public function getTemplateTypes(\XF\Entity\Style $style = null)
    {
        $types = parent::getTemplateTypes($style);

        if (!isset($types['admin']))
        {
            $types['admin'] = \XF::phrase('admin');
        }

        return $types;
    }
}
