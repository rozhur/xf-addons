<?php

namespace ZD\IC\Cli;

use XF\Container;

class App extends \XF\Cli\App
{
    public function initializeExtra()
    {
        parent::initializeExtra();

        $this->container['worker'] = function (Container $c)
        {
            return $this->extendClass('');
        };
    }

    public function start($allowShortCircuit = false)
    {
        parent::start($allowShortCircuit);
    }

    public function getVisitorFromSession(\XF\Session\Session $session, array $extraWith = [])
    {
        return parent::getVisitorFromSession($session, $extraWith);
    }
}