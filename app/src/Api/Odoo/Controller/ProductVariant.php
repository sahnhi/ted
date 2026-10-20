<?php

namespace App\Api\Odoo\Controller;

use App\Api\Odoo\Model\ProductVariant as ProductVariantModel;
use App\Api\Odoo\Service\ChannelPricingPriceUpdater;
use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\ProductCombo;
use App\Doctrine\Entity\ProductVariantEcotax;
use App\Doctrine\Entity\Sylius\ChannelPricing;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\ProductComboRepository;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use App\Doctrine\Repository\Sylius\ShippingCategoryRepository;
use App\Services\Channel\ChannelCode;
use App\Services\Mail\Mailer\ProductUpdatedAlert;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Product\Repository\ProductRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\InvalidArgumentException;

class ProductVariant extends AbstractOdooController
{
    public function __construct(
        string $apiKey,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly RepositoryInterface $productOptionRepository,
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly ProductVariantFactoryInterface $productVariantFactory,
        private readonly ShippingCategoryRepository $shippingCategoryRepository,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly EntityRepository $channelPricingRepository,
        private readonly ProductComboRepository $productComboRepository,
        private readonly EntityRepository $ecotaxRepository,
        private readonly EntityRepository $productVariantEcotaxRepository,
        private readonly EntityRepository $taxCategoryRepository,
        LoggerInterface $odooLogger,
        private readonly ProductUpdatedAlert $productUpdatedAlert,
        private readonly EntityRepository $bundleAssociationRepository,
        private readonly ChannelPricingPriceUpdater $channelPricingPriceUpdater,
    ) {
        $this->apiKey = $apiKey;
        parent::__construct($odooLogger, $apiKey);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(Request $request): Response
    {
        $this->logRequest($request);

        try {
            $this->authenticate($request);

            $productVariantModel = new ProductVariantModel($request);
            if (empty($productVariantModel->sku)) {
                throw new InvalidArgumentException('Product variant sku cannot be empty', Response::HTTP_BAD_REQUEST);
            }

            $product = $this->productRepository->findOneBy(['code' => $productVariantModel->code]);
            if (null === $product) {
                throw new InvalidArgumentException(sprintf("Product %s doesn't exist", $productVariantModel->code), Response::HTTP_BAD_REQUEST);
            }

            /** @var ProductVariantInterface|null $productVariant */
            $productVariant = $this->productVariantRepository->findOneBy([
                'code' => $productVariantModel->sku,
            ]);

            if ($productVariant?->isBundle()) {
                throw new InvalidArgumentException('Bundle update is no longer supported', Response::HTTP_BAD_REQUEST);
            }

            $taxCategory = $this->taxCategoryRepository->findOneBy(['code' => 'TVA']);

            $new = false;
            if (!$productVariant) {
                $new = true;
                $productVariant = $this->productVariantFactory->createNew();
                $productVariant->setShippingRequired(true);
                $productVariant->setEnabled(false);
            }

            $productVariant->setName($productVariantModel->name);
            $productVariant->setCode($productVariantModel->sku);
            $productVariant->setProduct($product);
            $productVariant->setWidth((float) $productVariantModel->width);
            $productVariant->setHeight((float) $productVariantModel->height);
            $productVariant->setDepth(floatval($productVariantModel->length));
            $productVariant->setWeight(floatval($productVariantModel->weight));
            $productVariant->setTaxCategory($taxCategory);

            $shippingCategory = $this->shippingCategoryRepository->findOneBy(['code' => $productVariantModel->deliveryCategory]);
            $productVariant->setShippingCategory($shippingCategory);

            /* ProductVariant option value */
            if (!empty($productVariantModel->options)) {
                foreach ($productVariantModel->options as $row) {
                    $productOption = $this->productOptionRepository->findOneBy(['code' => $row['code']]);
                    $optionValue = null;
                    if (null !== $productOption && !empty($productOption->getValues())) {
                        foreach ($productOption->getValues() as $value) {
                            if ($row['value'] === $value->getCode()) {
                                $optionValue = $value;
                                break;
                            }
                        }
                        if (null !== $optionValue && !$productVariant->hasOptionValue($optionValue)) {
                            $productVariant->addOptionValue($optionValue);
                        }
                    }
                }
            }

            /* Prices */
            $channels = $this->channelRepository->findAll();
            foreach ($channels as $channel) {
                $price = in_array($channel->getCode(), ChannelCode::NOT_SELL_CHANNELS) ?
                    0 :
                    $productVariantModel->salePrice;
                /** @var Channel $channel */
                $channelPricing = $this->channelPricingRepository->findOneBy(['channelCode' => $channel->getCode(), 'productVariant' => $productVariant]);
                if (!$channelPricing) {
                    $channelPricing = new ChannelPricing();
                } elseif ($channelPricing->getPrice() !== $price) {
                    $bundleAssociations = $this->bundleAssociationRepository->findBy(['variant' => $productVariant]);
                    if (!empty($bundleAssociations)) {
                        foreach ($bundleAssociations as $bundleAssociation) {
                            if ($price !== $bundleAssociation->getPrice() && 0 !== $bundleAssociation->getPrice()) {
                                foreach ($channels as $channelBundle) {
                                    /** @var Channel $channelBundle */
                                    $bundleChannelPricing = $this->channelPricingRepository->findOneBy(['channelCode' => $channelBundle->getCode(), 'productVariant' => $bundleAssociation->getBundle()]);
                                    if (!empty($bundleChannelPricing) && 0 !== $bundleChannelPricing->getPrice()) {
                                        $bundleChannelPricing->setPrice($bundleChannelPricing->getPrice() - $bundleAssociation->getPrice() + $price);
                                        $this->entityManager->persist($bundleChannelPricing);
                                    }
                                }
                                $bundleAssociation->setPrice($price);
                            }
                        }
                    }
                }

                $channelPricing->setChannelCode($channel->getCode());
                $channelPricing->setProductVariant($productVariant);

                $this->channelPricingPriceUpdater->updateChannelPricing($channelPricing, $price);

                $this->entityManager->persist($channelPricing);
            }

            /* Product Combo */
            foreach ($productVariantModel->billOfMaterial['data'] as $kit) {
                $variant = $this->productVariantRepository->findOneBy(['code' => $kit['sku']]);

                if (null !== $variant) {
                    $variantsLogistics = $this->getVariantLogistics($variant);

                    foreach ($variantsLogistics as $variantLogisticArray) {
                        /** @var ProductVariantInterface $variantLogistic */
                        $variantLogistic = $variantLogisticArray['variant'];
                        $combo = $this->createProductCombo($productVariant, $variantLogistic, $kit['qty'] * $variantLogisticArray['quantity']);
                        $this->entityManager->persist($combo);
                    }

                    if (empty($variantsLogistics)) {
                        $combo = $this->createProductCombo($productVariant, $variant, $kit['qty']);
                        $this->entityManager->persist($combo);
                    }

                    $this->handleAssociations($variant, $productVariant, $kit['qty']);
                }
            }

            /* Ecotax */
            $ecotaxes = [];
            $ecotaxTotal = 0;
            if (!empty($productVariantModel->ecotax)) {
                foreach ($productVariantModel->ecotax as $ecotaxCode) {
                    $data = explode('Q', (string) $ecotaxCode['code']);
                    $ecotaxes[$data[1]] = $data[0];
                }
                foreach ($ecotaxes as $code => $quantity) {
                    $ecotax = $this->ecotaxRepository->findOneBy(['code' => $code]);
                    $productVariantEcotax = $this->productVariantEcotaxRepository->findOneBy(['ecotax' => $ecotax, 'variant' => $productVariant]);
                    if (!$productVariantEcotax) {
                        $productVariantEcotax = new ProductVariantEcotax();
                        $productVariantEcotax->setEcotax($ecotax);
                        $productVariantEcotax->setVariant($productVariant);
                    }
                    $productVariantEcotax->setQuantity($quantity);
                    $ecotaxTotal += $quantity * $ecotax->getAmountTTC();
                    $this->entityManager->persist($productVariantEcotax);
                }
                $productVariant->setEcotax($ecotaxTotal);
            }

            $this->entityManager->persist($productVariant);
            $this->entityManager->flush();

            if (!$new) {
                $this->productUpdatedAlert->generateEmail(ProductUpdatedAlert::TYPE_VARIANT, $productVariant->getCode(), 'Odoo');
            }
        } catch (\Exception $exception) {
            $this->logError($request, $exception->getCode(), $exception->getMessage());

            return new Response($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('ok', Response::HTTP_OK);
    }

    private function handleAssociations(ProductVariantInterface $variantLogistic, ProductVariantInterface $productVariant, int $quantity): void
    {
        $product = $productVariant->getProduct();

        if (!str_contains((string) $product->getCode(), ProductVariantInterface::BUNDLE_PREFIX)) {
            return;
        }

        $bundleAssociation = $this->bundleAssociationRepository->findOneBy(['variant' => $variantLogistic, 'bundle' => $productVariant]);

        if (null === $bundleAssociation) {
            $bundleAssociation = new BundleAssociation();
        }

        /** @var ChannelPricingInterface $price */
        $price = $this->channelPricingRepository->findOneBy(['productVariant' => $variantLogistic, 'channelCode' => ChannelCode::CHANNEL_WEB]);

        $bundleAssociation->setBundle($productVariant);
        $bundleAssociation->setVariant($variantLogistic);
        $bundleAssociation->setQuantity($quantity);
        $bundleAssociation->setPrice($price ? $price->getPrice() : 0);

        $this->entityManager->persist($bundleAssociation);
    }

    private function getVariantLogistics(ProductVariantInterface $variant): array
    {
        $variantsLogistics = [];
        $productCombos = $this->productComboRepository->findBy(['productVariant' => $variant]);

        /** @var ProductCombo $productCombo */
        foreach ($productCombos as $productCombo) {
            $subLevel = $productCombo->getProductVariantLogistic() ?
                $this->getVariantLogistics($productCombo->getProductVariantLogistic()) : [];

            if (empty($subLevel)) {
                $variantLogistic = $productCombo->getProductVariantLogistic();

                if ($productCombo->getQuantity() >= 1 && null !== $variantLogistic) {
                    if (!isset($variantsLogistics[$variantLogistic->getCode()])) {
                        $variantsLogistics[$variantLogistic->getCode()] = [
                            'variant' => $variantLogistic,
                            'quantity' => $productCombo->getQuantity(),
                        ];
                    } else {
                        $variantsLogistics[$variantLogistic->getCode()]['quantity'] += $productCombo->getQuantity();
                    }
                }
            } else {
                $variantsLogistics = array_merge($variantsLogistics, $subLevel);
            }
        }

        return $variantsLogistics;
    }

    private function createProductCombo(ProductVariantInterface $productVariant, ProductVariantInterface $variantLogistic, float $quantity): ProductCombo
    {
        $productCombo = $this->productComboRepository->findOneBy(['productVariant' => $productVariant, 'productVariantLogistic' => $variantLogistic]);
        if (!$productCombo) {
            $productCombo = new ProductCombo();
            $productCombo->setProductVariant($productVariant);
            $productCombo->setProductVariantLogistic($variantLogistic);
        }
        $productCombo->setQuantity($quantity);

        return $productCombo;
    }
}
