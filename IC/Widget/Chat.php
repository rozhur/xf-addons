<?php

namespace ZD\IC\Widget;

use XF\Widget\AbstractWidget;

class Chat extends AbstractWidget
{
    public function getDefaultTitle()
    {
        return \XF::phrase('zdic_chat');
    }

    public function render()
    {
        return $this->renderer('zdic_widget_chat')->render();
    }
}