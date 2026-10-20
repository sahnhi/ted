<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Factory\Order\OrderMessage\OrderMessageFactory;
use App\Processor\OrderMessage\Processors\Interfaces\OrderMessageProcessorItemInterface;

class NonMatchingBlanketSheetSizesOrderMessageProcessor implements OrderMessageProcessorItemInterface
{
    public function __construct(
        private readonly array $blanketsProductCodeAndSizeOptionCodes,
        private readonly array $blanketsSheetsProductCodeAndSizeOptionCodes,
        private readonly OrderMessageFactory $orderMessageFactory,
    ) {
    }

    public function process(OrderInterface $order): void
    {
        $blanketSizes = $sheetSizes = [];

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            /** @var ProductVariant|null $variant */
            $variant = $item->getVariant();
            if (null === ($product = $variant?->getProduct())) {
                continue;
            }

            $productCode = $product->getCode();

            $sizeOptionCode = $this->blanketsProductCodeAndSizeOptionCodes[$productCode]
                ?? $this->blanketsSheetsProductCodeAndSizeOptionCodes[$productCode]
                ?? null;

            if (null === $sizeOptionCode) {
                continue;
            }

            $size = $this->getSizeFromOptionValues($variant->getOptionValues(), $sizeOptionCode);
            if (null === $size) {
                continue;
            }

            if (array_key_exists($productCode, $this->blanketsProductCodeAndSizeOptionCodes)) {
                $blanketSizes[] = $size;

                continue;
            }

            if (array_key_exists($productCode, $this->blanketsSheetsProductCodeAndSizeOptionCodes)) {
                $sheetSizes[] = $size;
            }
        }

        // If we only have blankets, OR only have sheets, OR none of them, we don't need to do anything
        if ([] === $blanketSizes || [] === $sheetSizes) {
            return;
        }

        foreach ($blanketSizes as $blanketSize) {
            if (!in_array($blanketSize, $sheetSizes, true)) {
                $order->addMessage($this->orderMessageFactory->createNewAlert(
                    'Vous avez choisi des tailles de couette et housse de couette différentes.'
                ));

                return;
            }
        }
    }

    /**
     * @param iterable<ProductOptionValue> $optionsValues
     */
    private function getSizeFromOptionValues(iterable $optionsValues, string $sizeOptionCode): ?string
    {
        foreach ($optionsValues as $optionValue) {
            if ($optionValue->getOption()?->getCode() === $sizeOptionCode) {
                return $optionValue->getValue();
            }
        }

        return null;
    }
}
