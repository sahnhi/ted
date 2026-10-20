<?php

namespace App\Command\Cron\Review;

use App\Services\Cron\Review\Preview\PreviewRssXmlPublisherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateRssZipFileCommand extends Command
{
    protected static $defaultName = 'app:cron:review:google-rss';

    public function __construct(
        private readonly PreviewRssXmlPublisherInterface $rssXmlPublisher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Review Google RSS');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->rssXmlPublisher->publishFirst();

        return 0;
    }
}
