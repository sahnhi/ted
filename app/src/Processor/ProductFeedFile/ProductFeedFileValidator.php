<?php

declare(strict_types=1);

namespace App\Processor\ProductFeedFile;

use App\Event\ProductFeedFile\PreValidatorEvent;
use App\Processor\ProductFeedFile\Validators\ProductFeedFileValidatorElementInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductFeedFileValidator implements ProductFeedFileValidatorInterface
{
    public function __construct(
        private readonly iterable $validators,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function validate(string $file): void
    {
        $validatedFile = new PreValidatorEvent($file);
        $this->eventDispatcher->dispatch($validatedFile);

        foreach ($this->validators as $validator) {
            if (!$validator instanceof ProductFeedFileValidatorElementInterface) {
                throw new \InvalidArgumentException('A validator class must implement ProductFeedFileValidatorElementInterface.');
            }
            $validator->validate($validatedFile->getFile());
        }
    }
}
