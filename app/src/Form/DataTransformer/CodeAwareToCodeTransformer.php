<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CodeAwareToCodeTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly RepositoryInterface $repository
    ) {
    }

    public function transform($value): object|null
    {
        if (null === $value) {
            return null;
        }

        return $this->repository->findOneBy(['code' => $value]);
    }

    public function reverseTransform($value): string|null
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof CodeAwareInterface) {
            throw new TransformationFailedException('Expected an instance of CodeAwareInterface.');
        }

        return $value->getCode();
    }
}
