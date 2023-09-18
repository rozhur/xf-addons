<?php

namespace ZD\ESS\Alert;

use XF\Alert\AbstractHandler;

class Warning extends AbstractHandler
{
    public function getOptOutActions()
    {
        return [
            'insert'
        ];
    }

    public function getOptOutDisplayOrder()
    {
        return 100;
    }
}