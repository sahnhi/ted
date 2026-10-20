<?php

namespace App\Command\Cron\Mail;

use App\Services\Cron\Mail\CheckSpidyEdi;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckSpidyEdiCommand extends Command
{
    protected static $defaultName = 'app:cron:mail:check-spidy-edi';

    public function __construct(
        private readonly CheckSpidyEdi $checkSpidyEdi
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Mail CheckSpidyEdi');
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkSpidyEdi->process();

        return 0;
    }
}
