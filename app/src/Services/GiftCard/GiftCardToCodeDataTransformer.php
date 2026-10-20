<?php

namespace App\Services\GiftCard;

use Setono\SyliusGiftCardPlugin\Form\DataTransformer\GiftCardToCodeDataTransformer as BaseDataTransformer;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class GiftCardToCodeDataTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly BaseDataTransformer $decoratedDataTransformer,
        private readonly GiftCardRepositoryInterface $giftCardRepository
    ) {
    }

    public function reverseTransform($value): ?GiftCardInterface
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $giftCard = $this->giftCardRepository->findOneBy([
            'enabled' => true,
            'code' => $value,
        ]);

        if (null !== $giftCard) {
            return $giftCard;
        }

        throw new TransformationFailedException('setono_sylius_gift_card.ui.gift_card_code_does_not_exist');
    }

    public function transform($value): ?string
    {
        return $this->decoratedDataTransformer->transform($value);
    }
}
