<?php

namespace ZD\TA\Pub\Controller;

use XF\Mvc\Controller;

class Faq extends Controller
{
    public function actionXfAddon()
    {
        $id = strtolower($this->filter('id', 'str'));

        switch ($id)
        {
            case 'zdis': return $this->redirect('https://xf.zhdev.org/resource1');
            case 'zdir': return $this->redirect('https://xf.zhdev.org/resource4');
        }

        return $this->redirect('');
    }
}