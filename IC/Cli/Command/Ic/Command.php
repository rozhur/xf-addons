<?php

namespace ZD\IC\Cli\Command\Ic;

use XF\Cli\Command\CustomAppCommandInterface;

abstract class Command extends \Symfony\Component\Console\Command\Command implements CustomAppCommandInterface
{
    public static function getCustomAppClass()
    {
        return 'ZD\IC\Cli\App';
    }
}