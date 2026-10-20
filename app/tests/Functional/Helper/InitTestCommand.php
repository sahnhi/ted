<?php

declare(strict_types=1);

namespace App\Tests\Functional\Helper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitTestCommand extends Command
{
    public function __construct(
        private readonly FixtureLoader $fixtureLoader
    ) {
        parent::__construct('init:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fixtureLoader->load();

        return 0;
    }
}
