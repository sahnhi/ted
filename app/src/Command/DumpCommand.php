<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    public function __construct(
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:command:dumb')
            ->setDescription('Dump command which can be used for testing without need to create a new command')
            ->setHelp('This command is only intended for development purposes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
