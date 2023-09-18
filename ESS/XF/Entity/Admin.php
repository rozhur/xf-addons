<?php 

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property array $zdess_available_moderator_permissions
 */
class Admin extends XFCP_Admin
{
    protected function verifyZdisAdminStyleId(&$styleId)
    {
        if ($styleId)
        {
            $style = $this->_em->find('XF:Style', $styleId);
            if (!$style)
            {
                $styleId = 0;
            }
            else if (!$this->hasAdminPermission('style'))
            {
                if (!$style->user_selectable)
                {
                    $styleId = $this->zdess_admin_style_id;
                }
            }
        }

        return true;
    }

    protected function verifyAdminLanguageId(&$languageId)
    {
        if ($languageId)
        {
            $language = $this->_em->find('XF:Language', $languageId);
            if (!$language)
            {
                $languageId = 0;
            }
            else if (!$this->hasAdminPermission('language'))
            {
                $language = $this->_em->find('XF:Language', $languageId);
                if (!$language->user_selectable)
                {
                    $languageId = $this->admin_language_id;
                }
            }
        }

        return true;
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_available_moderator_permissions'] = ['type' => self::JSON_ARRAY, 'default' => []];

        return $structure;
    }
}
