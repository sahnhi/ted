<?php

namespace App\Command\Cron\Review;

use App\Services\Cron\Review\Retrieve;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RetrieveCommand extends Command
{
    protected static $defaultName = 'app:cron:review:retrieve';

    public function __construct(
        private readonly Retrieve $retrieve
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Review Retrieve');
    }

    /**
     * @throws OptimisticLockException
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->retrieve->process();

        return 0;
    }
}
