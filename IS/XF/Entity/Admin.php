<?php 

namespace ZD\IS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int $zdis_admin_style_id
 */
class Admin extends XFCP_Admin
{
    protected function verifyZdisAdminStyleId(&$styleId)
    {
        if ($styleId && !$this->_em->find('XF:Style', $styleId))
        {
            $styleId = 0;
        }

        return true;
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdis_admin_style_id'] = ['type' => self::UINT, 'default' => 0];

        return $structure;
    }
}
