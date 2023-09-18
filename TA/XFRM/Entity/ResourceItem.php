<?php

namespace ZD\TA\XFRM\Entity;

use XF\Mvc\Entity\Structure;

class ResourceItem extends XFCP_ResourceItem
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->options['zdta_update_first_post'] = false;

        return $structure;
    }

    protected function getThreadMessage()
    {
        $snippet = $this->app()->bbCode()->render(
            $this->app()->stringFormatter()->wholeWordTrim($this->Description->message, 500),
            'bbCodeClean',
            'post',
            null
        );

        $phrase = \XF::phrase('xfrm_resource_thread_create', [
            'title' => $this->title_,
            'tag_line' => $this->tag_line_,
            'username' => $this->User ? $this->User->username : $this->username,
            'snippet' => $snippet,
            'resource_link' => $this->app()->router('public')->buildLink('canonical:resources', $this)
        ]);

        return $phrase->render('raw');
    }

    protected function _postSave()
    {
        parent::_postSave();

        if ($this->getOption('zdta_update_first_post'))
        {
            $post = $this->Discussion->FirstPost;

            /** @var \XF\Service\Post\Editor $editor */
            $editor = $this->app()->service('XF:Post\Editor', $post);
            $editor->setMessage($this->getThreadMessage());
            $editor->save();
        }
    }
}
