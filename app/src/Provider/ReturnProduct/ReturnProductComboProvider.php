<?php

namespace App\Provider\ReturnProduct;

use App\Doctrine\Entity\ProductCombo;
use App\Factory\ReturnProduct\ReturnProductComboFactory;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnProductComboProvider implements ReturnProductComboProviderInterface
{
    public function __construct(
        private readonly RepositoryInterface $productComboRepository,
        private readonly ReturnProductComboFactory $returnProductComboFactory,
        private readonly ReturnProductVariantProviderInterface $returnProductVariantProvider
    ) {
    }

    public function provide($originalVariant, $variant): ?ProductCombo
    {
        $combo = $this->productComboRepository->findByVariantCode($variant->getCode());

        if (empty($combo)) {
            $combo = $this->provideNew($originalVariant, $variant);
        } else {
            $combo = $combo[0];
        }

        return $combo;
    }

    public function provideNew($originalVariant, $variant): ?ProductCombo
    {
        $originalCombo = $this->productComboRepository->findByVariantCode($originalVariant->getCode());

        if (empty($originalCombo)) {
            return null;
        }

        /** @var ProductCombo $originalCombo */
        $originalCombo = $originalCombo[0];

        /** @var ProductCombo $combo */
        $combo = $this->returnProductComboFactory->createNewFromExisting($originalCombo);
        $combo->setProductVariant($variant);

        if (null !== $originalCombo->getProductVariantLogistic()) {
            $variantLogistic = $this->returnProductVariantProvider->provide($originalCombo->getProductVariantLogistic(), $variant->getProduct());
            $combo->setProductVariantLogistic($variantLogistic);
        }

        $this->productComboRepository->add($combo);

        return $combo;
    }
}
