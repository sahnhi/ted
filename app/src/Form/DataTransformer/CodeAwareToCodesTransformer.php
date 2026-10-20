<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ObjectRepository;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Webmozart\Assert\Assert;

class CodeAwareToCodesTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly ObjectRepository $repository
    ) {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function transform($value): Collection
    {
        Assert::nullOrIsArray($value);

        if (empty($value)) {
            return new ArrayCollection();
        }

        return new ArrayCollection($this->repository->findBy(['code' => $value]));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function reverseTransform($value): array
    {
        Assert::isInstanceOf($value, Collection::class);

        $codes = [];

        /** @var CodeAwareInterface $codeAwareClass */
        foreach ($value as $codeAwareClass) {
            $codes[] = $codeAwareClass->getCode();
        }

        return $codes;
    }
}
