<?php

declare(strict_types=1);

namespace App\Channable\MessageHandler;

use App\Channable\Entity\ChannableFileRow;
use App\Channable\Message\ExportProductRow;
use App\Doctrine\Entity\ProductFeedFile;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Core\Calculator\ProductVariantPriceCalculatorInterface;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ExportProductRowHandler implements MessageHandlerInterface
{
    private readonly ChannelInterface $channel;

    public function __construct(
        private readonly RepositoryInterface $productFeedFileRepository,
        private readonly RepositoryInterface $channableFileLogRepository,
        private readonly RepositoryInterface $channableFileRowRepository,
        private readonly FactoryInterface $channableFileRowFactory,
        private readonly ProductVariantPriceCalculatorInterface $productVariantPricesCalculator,
        private readonly RepositoryInterface $channelRepository,
    ) {
        $this->channel = $this->channelRepository->findOneBy(['code' => 'default']);
    }

    public function __invoke(ExportProductRow $exportProductRow): void
    {
        /**
         * @var ProductFeedFile $product
         */
        $product = $this->productFeedFileRepository->find($exportProductRow->getProductRowId());
        $variant = $product->getProductVariant();
        $channableFileLog = $this->channableFileLogRepository->find($exportProductRow->getChannableFileLogId());

        /**
         * @var ChannableFileRow $row
         */
        $row = $this->channableFileRowFactory->createNew();
        $row
            ->setVariant($variant)
            ->setGoogleProductId((string) $product->getGoogleId())
            ->setChannableFileLog($channableFileLog)
            ->setSku($variant->getCode())
            ->setGtin((string) $variant->getEan13())
            ->setParentSku($variant->getProduct()->getCode())
            ->setTitle($product->getProductVariant()->getName())
            ->setUrlProduct($product->getUrl())
            ->setDescription($product->getDescription())
            ->setState($product->getObjectState())
            ->setProductHighlight($product->getHighlight())
            ->setGoogleProductCategory($product->getGoogleCategory())
            ->setProductType($product->getProductType())
            ->setBundle($variant->isBundle())
            ->setPublic('Adult')
            ->setBrand('Tediber')
            ->setGender('Mixte')
            ->setAvailability('in stock')
            ->setPriceWithVat($this->productVariantPricesCalculator->calculate($variant, ['channel' => $this->channel]))
            ->setPromotionPriceWithVat($this->getPromotionPrice($variant))
            ->setCurrency('EUR')
            ->setShipping('FR:::0.00 EUR')
            ->setColor($product->getColor())
            ->setSize($this->getFormattedSize($variant))
            ->setMaterial($product->getComposition())
            ->setThickness((string) $variant->getHeight())
            ->setMadeIn('France')
            ->setExpeditionCountry('France')
            ->setProductDetails('')
            ->setProductLength((string) $variant->getDepth())
            ->setProductWidth((string) $variant->getWidth())
            ->setProductHeight((string) $variant->getHeight())
            ->setProductWeight((string) $variant->getWeight())
            ->setProductCharacteristics('')
            ->setCertifications('')
            ->setPromotion($product->getPromotionIdentifier())
            ->setMobileUrl($product->getUrl());

        $this->addImages($row, $product);

        $this->channableFileRowRepository->add($row);
    }

    private function addImages(ChannableFileRow $row, ProductFeedFile $product): void
    {
        // always the  main image on first position
        $row->addImage($product->getMainImage());

        $i = 1;
        foreach ($product->getAdditionalImages() as $image) {
            if (6 >= $i++) {
                $row->addImage($image->getUrl());
            }
        }
    }

    private function getFormattedSize(ProductVariantInterface $variant): string
    {
        $width = $variant->getWidth();
        $depth = $variant->getHeight();
        if (empty($width) || empty($depth)) {
            return '';
        }

        return sprintf('%s x %s cm', $width, $depth);
    }

    private function getPromotionPrice(ProductVariantInterface $variant): ?int
    {
        $promotionPrice = null;

        if ($this->productVariantPricesCalculator instanceof ProductVariantPricesCalculatorInterface) {
            $promotionPrice = $this->productVariantPricesCalculator->calculateOriginal($variant, ['channel' => $this->channel]);
        }

        $price = $this->productVariantPricesCalculator->calculate($variant, ['channel' => $this->channel]);

        return $promotionPrice === $price ? null : $promotionPrice;
    }
}
