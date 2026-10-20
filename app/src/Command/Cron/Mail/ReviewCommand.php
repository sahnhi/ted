<?php

namespace App\Command\Cron\Mail;

use App\Services\Cron\Review\Seek;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ReviewCommand extends Command
{
    protected static $defaultName = 'app:cron:mail:review';

    public function __construct(
        private readonly Seek $review
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Mail Review');
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->review->process();

        return 0;
    }
}
