<?php

namespace App\Command\Cron\Mail;

use App\Services\Cron\Mail\Shipped;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ShippedCommand extends Command
{
    protected static $defaultName = 'app:cron:mail:shipped';

    public function __construct(
        private readonly Shipped $shipped
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Mail Shipped');
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->shipped->process();

        return 0;
    }
}
