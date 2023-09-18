<?php

namespace ZD\TA\Pub\Controller;

use XF\Mvc\Controller;

class Support extends Controller
{
    public function actionXfAddon()
    {
        $id = strtolower($this->filter('id', 'str'));
        return $this->redirect('https://xf.zhdev.org/' . $id);
    }
}