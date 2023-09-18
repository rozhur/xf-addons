<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property string $title_
 *
 * GETTERS
 * @property string $title
 *
 * RELATIONS
 * @property \XF\Entity\Phrase $MasterTitle
 */
class UserTitleLadder extends XFCP_UserTitleLadder
{
    public function getTitle()
    {
        $phrase = \XF::phrase($this->getPhraseName())->render('html', ['nameOnInvalid' => false]);
        return empty($phrase) ? $this->title_ : $phrase;
    }

    public function getPhraseName()
    {
        return 'user_title_ladder.' . $this->minimum_level;
    }

    public function getMasterPhrase($update = false)
    {
        $phrase = $this->MasterTitle;
        if (!$phrase)
        {
            /** @var \XF\Entity\Phrase $phrase */
            $phrase = $this->_em->create('XF:Phrase');
            $phrase->addon_id = $this->_getDeferredValue(function() { return 'ZD/ESS'; });
            $phrase->title = $this->_getDeferredValue(function() { return $this->getPhraseName(); });
            $phrase->phrase_text = $this->_getDeferredValue(function() { return $this->getValue('title'); });
            $phrase->language_id = 0;
        }
        else if ($update)
        {
            $phrase->title = $this->getPhraseName();
            $phrase->phrase_text = $this->getValue('title');
        }

        return $phrase;
    }

    protected function _postSave()
    {
        parent::_postSave();

        $this->getMasterPhrase(true)->save();
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $this->getMasterPhrase()->delete();
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['title'] = true;
        $structure->getters['user_title'] = true;
        $structure->getters['banner_text'] = true;

        $structure->relations['MasterTitle'] = [
            'entity' => 'XF:Phrase',
            'type' => self::TO_ONE,
            'conditions' => [
                ['language_id', '=', 0],
                ['title', '=', 'user_title_ladder.', '$minimum_level']
            ]
        ];

        return $structure;
    }
}