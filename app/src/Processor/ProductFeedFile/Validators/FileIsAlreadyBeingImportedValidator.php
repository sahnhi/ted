<?php

declare(strict_types=1);

namespace App\Processor\ProductFeedFile\Validators;

use App\Processor\ProductFeedFile\Exceptions\ProductFeedFileValidatorException;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class FileIsAlreadyBeingImportedValidator implements ProductFeedFileValidatorElementInterface
{
    private const EXCEPTION_MESSAGE = 'A File is already being imported';

    public function __construct(
        private readonly RepositoryInterface $productFeedFileImporterRepository
    ) {
    }

    public function validate(string $file): bool
    {
        if ($this->isFileAlreadyBeingImported()) {
            throw new ProductFeedFileValidatorException(self::EXCEPTION_MESSAGE);
        }

        return true;
    }

    private function isFileAlreadyBeingImported(): bool
    {
        return null !== $this->productFeedFileImporterRepository->findOneBy(['state' => 'processing']);
    }
}
