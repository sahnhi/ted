<?php

declare(strict_types=1);

namespace App\Command;

use App\Processor\ProductFeedFile\ProductFeedFileImporterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ProductFeedFileImporterCommand extends Command
{
    private const PATH_ARGUMENT = 'path';
    private const AUTHOR_ARGUMENT = 'author';

    protected static $defaultName = 'app:tediber:product-feed-file-importer';

    public function __construct(
        private readonly ProductFeedFileImporterInterface $productFeedFileImporter
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Import a product feed file')
            ->addArgument(self::PATH_ARGUMENT, InputArgument::REQUIRED, 'The path to the product feed file')
            ->addArgument(self::AUTHOR_ARGUMENT, InputArgument::REQUIRED, 'The author of the product feed file import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->productFeedFileImporter->import($input->getArgument(self::PATH_ARGUMENT), $input->getArgument(self::AUTHOR_ARGUMENT));

        return 0;
    }
}
