<?php

namespace ZD\ESS\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property string $title_
 * @property string $user_title_
 * @property string $banner_text_
 * @property bool $zdess_disable_grouping
 * @property bool $zdess_super_user_group
 *
 * GETTERS
 * @property string $title
 * @property string $user_title
 * @property string $banner_text
 *
 * RELATIONS
 * @property \XF\Entity\Phrase $MasterTitle
 */
class UserGroup extends XFCP_UserGroup
{
    public function getTitle()
    {
        $phrase = \XF::phrase($this->getPhraseName('title'))->render('html', ['nameOnInvalid' => false]);
        return empty($phrase) ? $this->title_ : $phrase;
    }

    public function getUserTitle()
    {
        $phrase = \XF::phrase($this->getPhraseName('user_title'))->render('html', ['nameOnInvalid' => false]);
        return empty($phrase) ? $this->user_title_ : $phrase;
    }

    public function getBannerText()
    {
        $phrase = \XF::phrase($this->getPhraseName('banner_text'))->render('html', ['nameOnInvalid' => false]);
        return empty($phrase) ? $this->banner_text_ : $phrase;
    }

    public function getPhraseName($title)
    {
        return 'user_group_' . $title . '.' . $this->user_group_id;
    }

    public function getMasterPhrase($relation, $title, $update = false)
    {
        $phrase = $this->getRelation($relation);
        if (!$phrase)
        {
            /** @var \XF\Entity\Phrase $phrase */
            $phrase = $this->_em->create('XF:Phrase');
            $phrase->addon_id = $this->_getDeferredValue(function() { return 'ZD/ESS'; });
            $phrase->title = $this->_getDeferredValue(function() use ($title) { return $this->getPhraseName($title); });
            $phrase->phrase_text = $this->_getDeferredValue(function() use ($title) { return $this->getValue($title); });
            $phrase->language_id = 0;
        }
        else if ($update)
        {
            /** @var \XF\Entity\Phrase $phrase */
            $phrase->title = $this->getPhraseName($title);
            $phrase->phrase_text = $this->getValue($title);
        }

        return $phrase;
    }

    public function saveMasterPhrases()
    {
        $this->getMasterPhrase('MasterTitle', 'title', true)->save();
        $this->getMasterPhrase('MasterUserTitle', 'user_title', true)->save();
        $this->getMasterPhrase('MasterBannerText', 'banner_text', true)->save();
    }

    public function deleteMasterPhrases()
    {
        $this->getMasterPhrase('MasterTitle', 'title')->delete();
        $this->getMasterPhrase('MasterUserTitle', 'user_title')->delete();
        $this->getMasterPhrase('MasterBannerText', 'banner_text')->delete();
    }

    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isChanged('zdess_disable_grouping'))
        {
            $this->rebuildUserBannerCache();
        }

        $this->saveMasterPhrases();
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $this->deleteMasterPhrases();
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['zdess_disable_grouping'] = ['type' => self::BOOL, 'default' => true];
        $structure->columns['zdess_super_user_group'] = ['type' => self::BOOL, 'default' => false];

        $structure->getters['title'] = true;
        $structure->getters['user_title'] = true;
        $structure->getters['banner_text'] = true;

        $structure->relations['MasterTitle'] = [
            'entity' => 'XF:Phrase',
            'type' => self::TO_ONE,
            'conditions' => [
                ['language_id', '=', 0],
                ['title', '=', 'user_group_title.', '$user_group_id']
            ]
        ];
        $structure->relations['MasterUserTitle'] = [
            'entity' => 'XF:Phrase',
            'type' => self::TO_ONE,
            'conditions' => [
                ['language_id', '=', 0],
                ['title', '=', 'user_group_user_title.', '$user_group_id']
            ]
        ];
        $structure->relations['MasterBannerText'] = [
            'entity' => 'XF:Phrase',
            'type' => self::TO_ONE,
            'conditions' => [
                ['language_id', '=', 0],
                ['title', '=', 'user_group_banner_text.', '$user_group_id']
            ]
        ];

        return $structure;
    }
}