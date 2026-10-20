<?php

namespace App\Command\Cron\Order;

use App\Services\FastmagImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportFastmagCommand extends Command
{
    protected static $defaultName = 'app:cron:order:fastmag-import';

    public function __construct(
        private readonly FastmagImport $fastmagImport
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Order Fastmag Import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fastmagImport->process();

        return 0;
    }
}
