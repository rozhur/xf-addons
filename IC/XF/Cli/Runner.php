<?php

namespace ZD\IC\XF\Cli;

use Symfony\Component\Console\Application as ConsoleApplication;

class Runner extends XFCP_Runner
{
    protected function registerCommands(ConsoleApplication $app)
    {
        parent::registerCommands($app);

        $app->add(new (\XF::extendClass('ZD\IC\Cli\Command\Ic\IcWorker')));
    }
}