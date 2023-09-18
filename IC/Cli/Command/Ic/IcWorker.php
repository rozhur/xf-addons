<?php

namespace ZD\IC\Cli\Command\Ic;

use Channel\Server;
use PHPSocketIO\ChannelAdapter;
use PHPSocketIO\Socket;
use PHPSocketIO\SocketIO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Workerman\Worker;
use XF\Entity\User;
use XF\Session\Session;
use ZD\IC\Cli\App;

class IcWorker extends Command
{
    protected function configure()
    {
        $this
            ->setName('ic:worker')
            ->setDescription('Manage worker')
            ->addArgument('start')
            ->addOption('g', 'g', InputOption::VALUE_NONE, 'Reload/stop gracefully')
            ->addOption('d', 'd', InputOption::VALUE_NONE, 'Start in daemon')
            ->addOption('p', 'p', InputOption::VALUE_REQUIRED, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var App $app */
        $app = \XF::app();

        $port = 2999;
        $channelAdapterPort = 3999;

        $io = new SocketIO($port);

        $io->on('workerStart', function() use ($channelAdapterPort, $io)
        {
            ChannelAdapter::$port = $channelAdapterPort;
            $io->adapter('\PHPSocketIO\ChannelAdapter');
        });

        $io->on('connection', function (Socket $socket) use ($channelAdapterPort, $app)
        {
            $app->em()->clearEntityCache();

            parse_str(str_replace('; ', '&', $socket->handshake['headers']['cookie'] ?? []), $cookie);

            $sessionClass = $app->extendClass('XF\Session\Session');

            /** @var Session $session */
            $session = new $sessionClass($app['session.public.storage'], ['cookie' => 'session']);
            $session->start(null, $cookie['xf_session'] ?? null);

            /** @var User $visitor */
            $visitor = $app->getVisitorFromSession($session);
            \XF::setVisitor($visitor);

            $socket->join($visitor->user_id);

            $app->db()->closeConnection();
        });

        new Server("127.0.0.1", $channelAdapterPort);

        $app->db()->closeConnection();

        /* Replace --x to -x and remove ic:worker arg for Worker command line */
        global $argv;
        unset($argv[1]);
        $argv = array_values($argv);
        $argv = preg_replace('/^-(-[a-z-])/', '$1', $argv);

        Worker::runAll();
        return 0;
    }
}