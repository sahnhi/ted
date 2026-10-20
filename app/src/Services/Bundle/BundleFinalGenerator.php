<?php

namespace App\Services\Bundle;

use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\BundleAssociationInterface;
use App\Doctrine\Entity\BundleGeneratorCombinationInterface;
use App\Doctrine\Entity\BundleGeneratorInterface;
use App\Doctrine\Entity\BundleGeneratorVariantInterface;
use App\Doctrine\Entity\ProductCombo;
use App\Doctrine\Entity\ProductVariantEcotax;
use App\Doctrine\Entity\Sylius\ChannelPricing;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\ProductComboRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Services\Channel\ChannelCode;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;
use Sylius\Component\Taxation\Repository\TaxCategoryRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BundleFinalGenerator implements BundleFinalGeneratorInterface
{
    public function __construct(
        private readonly ProductVariantFactoryInterface $productVariantFactory,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly ProductComboRepositoryInterface $productComboRepository,
        private readonly TaxCategoryRepositoryInterface $taxCategoryRepository,
        private readonly BundleCombinationGeneratorInterface $combinationGenerator,
        private readonly RepositoryInterface $channelPricingRepository,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly FlashBagInterface $flashBag,
        private readonly TranslatorInterface $translator,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function generate(ResourceControllerEvent $event): void
    {
        /** @var BundleGeneratorInterface $generator */
        $generator = $event->getSubject();

        $total = $generator->getCombinationsToGenerate()->count();
        $generated = 0;

        /** @var BundleGeneratorCombinationInterface $combination */
        foreach ($generator->getCombinationsToGenerate() as $combination) {
            $generated = $this->generateVariant($generator->getProduct(), $combination) ? $generated + 1 : $generated;
        }

        $this->flashBag->add('success', $generated . '/' . $total . ' ' . $this->translator->trans('app.ui.bundle_association.labels.variant_generated'));
    }

    private function generateVariant(ProductInterface $product, BundleGeneratorCombinationInterface $combination): bool
    {
        if ($this->combinationGenerator->doesCombinationAlreadyExists($product, $combination->getOptionValues()->toArray())
            || $this->productVariantRepository->findOneByCodeAndProductCode($combination->getCode(), $product->getCode())) {
            return false;
        }

        $variant = $this->productVariantFactory->createForProduct($product);

        try {
            /* @var ProductVariantInterface $variant */
            $variant->setCode($combination->getCode());
            $variant->setEnabled(false);
            $variant->setShippingCategory($combination->getBundleGenerator()->getShippingCategory());

            if ($variant->getShippingCategory() instanceof ShippingCategoryInterface) {
                $variant->setShippingRequired(true);
            }

            /** @var ProductOptionValueInterface $optionValue */
            foreach ($combination->getOptionValues() as $optionValue) {
                $variant->addOptionValue($optionValue);
            }

            $taxCategory = $this->taxCategoryRepository->findOneBy(['code' => 'TVA']);
            $variant->setTaxCategory($taxCategory);
            $variant->setName($product->getName());

            $this->generateBundleAssociation($variant, $combination);
            $this->generatePrices($variant);
            $this->generateProductVariantEcotax($variant);
            $this->generateWeight($variant);

            $this->productVariantRepository->add($variant);
            $this->generateProductCombo($variant);
        } catch (\Exception $exception) {
            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['bundle_generation' => $combination->getBundleGenerator()->getId()],
                [
                    'variant' => $variant,
                    'combination' => $combination,
                ]
            );

            return false;
        }

        return true;
    }

    /**
     * @param ProductVariant $bundle
     */
    private function generateBundleAssociation(ProductVariantInterface $bundle, BundleGeneratorCombinationInterface $combination): void
    {
        /** @var BundleGeneratorVariantInterface $variantGenerator */
        foreach ($combination->getVariants() as $variantGenerator) {
            /** @var ChannelPricingInterface|null $price */
            $price = $this->channelPricingRepository->findOneBy(['productVariant' => $variantGenerator->getVariant(), 'channelCode' => ChannelCode::CHANNEL_WEB]);

            $bundleAssociation = new BundleAssociation();
            $bundleAssociation->setBundle($bundle);
            $bundleAssociation->setVariant($variantGenerator->getVariant());
            $bundleAssociation->setQuantity($variantGenerator->getProduct()->getQuantity());
            $bundleAssociation->setPrice($variantGenerator->getProduct()->isFree() ? 0 : ($price instanceof ChannelPricingInterface ? $price->getPrice() : 0));
            $bundle->addBundleAssociation($bundleAssociation);
        }
    }

    private function generatePrices(ProductVariantInterface $variant): void
    {
        $channels = $this->channelRepository->findAll();

        /** @var ChannelInterface $channel */
        foreach ($channels as $channel) {
            $price = 0;
            $channelPricing = new ChannelPricing();
            $channelPricing->setProductVariant($variant);
            $channelPricing->setChannelCode($channel->getCode());

            /** @var BundleAssociationInterface $bundleAssociation */
            foreach ($variant->getBundleAssociations() as $bundleAssociation) {
                /** @var ChannelPricingInterface|null $compositionPricing */
                $compositionPricing = $this->channelPricingRepository->findOneBy(['productVariant' => $bundleAssociation->getVariant(), 'channelCode' => $channelPricing->getChannelCode()]);

                if ($bundleAssociation->getPrice() > 0) {
                    $price += $compositionPricing instanceof ChannelPricingInterface ? $compositionPricing->getPrice() * $bundleAssociation->getQuantity() : 0;
                }
            }

            $channelPricing->setPrice($price);
            $variant->addChannelPricing($channelPricing);
        }
    }

    /**
     * @param ProductVariant $variant
     */
    private function generateProductVariantEcotax(ProductVariantInterface $variant): void
    {
        /** @var BundleAssociationInterface $bundleAssociation */
        foreach ($variant->getBundleAssociations() as $bundleAssociation) {
            if ($bundleAssociation->getVariant() instanceof ProductVariantInterface) {
                /** @var ProductVariantEcotax $productVariantEcotax */
                foreach ($bundleAssociation->getVariant()->getProductVariantEcotax() as $productVariantEcotax) {
                    $newProductVariantEcotax = new ProductVariantEcotax();
                    $newProductVariantEcotax->setQuantity($productVariantEcotax->getQuantity());
                    $newProductVariantEcotax->setEcotax($productVariantEcotax->getEcotax());
                    $newProductVariantEcotax->setEcotax($productVariantEcotax->getEcotax());
                    $newProductVariantEcotax->setManufacturerPaid($productVariantEcotax->getManufacturerPaid());

                    $variant->addProductVariantEcotax($newProductVariantEcotax);
                }
            }
        }
    }

    /**
     * @param ProductVariant $variant
     */
    private function generateProductCombo(ProductVariantInterface $variant): void
    {
        /** @var BundleAssociationInterface $bundleAssociation */
        foreach ($variant->getBundleAssociations() as $bundleAssociation) {
            /** @var ProductVariant $logisticVariant */
            $logisticVariant = $bundleAssociation->getVariant();

            $productCombo = new ProductCombo();
            $productCombo->setProductVariant($variant);
            $productCombo->setProductVariantLogistic($logisticVariant);
            $productCombo->setQuantity($bundleAssociation->getQuantity());

            $this->productComboRepository->add($productCombo);
        }
    }

    private function generateWeight(ProductVariantInterface $variant): void
    {
        $weight = 0;

        /** @var BundleAssociationInterface $bundleAssociation */
        foreach ($variant->getBundleAssociations() as $bundleAssociation) {
            $logisticVariant = $bundleAssociation->getVariant();
            $weight += $logisticVariant->getWeight();
        }

        $variant->setWeight($weight);
    }
}
