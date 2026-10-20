<?php

namespace App\Services\Bundle;

use App\Doctrine\Entity\BundleGeneratorCombination;
use App\Doctrine\Entity\BundleGeneratorCombinationInterface;
use App\Doctrine\Entity\BundleGeneratorInterface;
use App\Doctrine\Entity\BundleGeneratorOptionInterface;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Webmozart\Assert\Assert;

class BundleCombinationGenerator implements BundleCombinationGeneratorInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository
    ) {
    }

    public function generateOptions(ResourceControllerEvent $event): void
    {
        /** @var BundleGeneratorInterface $generator */
        $generator = $event->getSubject();

        $combinations = $this->generateCombinationsOptions($generator);
        Assert::notEmpty($combinations);

        $generatorCombinations = [];

        foreach ($combinations as $combination) {
            $generatorCombinations[] = $this->generateCombination($generator, $combination);
        }

        $this->clearOtherCombinations($generator, $generatorCombinations);
    }

    private function generateCombinationsOptions(BundleGeneratorInterface $generator): array
    {
        $optionsArray = $this->provideOptionValuesArray($generator->getOptions());
        Assert::notEmpty($optionsArray);

        return $this->provideCombinationsOptions($optionsArray);
    }

    private function provideCombinationsOptions(array $options, ?string $code = null): array
    {
        if (null === $code) {
            $code = array_key_first($options);
            Assert::notNull($code);
        }

        if ($code === array_key_last($options)) {
            return $options[$code];
        }

        $codes = array_keys($options);

        // get combinations from subsequent arrays
        $tempArray = $this->provideCombinationsOptions($options, $codes[array_search($code, $codes) + 1]);
        $result = [];

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($options[$code] as $optionValue) {
            foreach ($tempArray as $temp) {
                $result[] = is_array($temp) ? array_merge([$optionValue], $temp) : [$optionValue, $temp];
            }
        }

        return $result;
    }

    private function provideOptionValuesArray(Collection $generatorOptions): array
    {
        $options = [];

        /** @var BundleGeneratorOptionInterface $generatorOption */
        foreach ($generatorOptions as $generatorOption) {
            $optionArray = [];

            if ($generatorOption->getProductOptionValue() instanceof ProductOptionValueInterface) {
                $optionArray[] = $generatorOption->getProductOptionValue();
            } else {
                foreach ($generatorOption->getProductOption()->getValues() as $optionValue) {
                    $optionArray[] = $optionValue;
                }
            }

            $options[$generatorOption->getProductOption()->getCode()] = $optionArray;
        }

        return $options;
    }

    private function generateCombination(BundleGeneratorInterface $generator, array $optionsValues): BundleGeneratorCombinationInterface
    {
        if (!($combination = $generator->getGeneratorCombinationForOptionValues($optionsValues)) instanceof BundleGeneratorCombinationInterface) {
            $combination = new BundleGeneratorCombination();
            $combination->setBundleGenerator($generator);

            /** @var ProductOptionValueInterface $optionValue */
            foreach ($optionsValues as $optionValue) {
                $combination->addOptionValue($optionValue);
            }

            $generator->addCombination($combination);
        }

        $combination->setAlreadyExists($this->doesCombinationAlreadyExists($generator->getProduct(), $optionsValues));
        if ($combination->isAlreadyExists()) {
            $combination->setWillBeGenerated(false);
        }

        return $combination;
    }

    public function doesCombinationAlreadyExists(ProductInterface $product, array $optionsValues): bool
    {
        $arrayOptions = [];

        /** @var ProductOptionValueInterface $optionValue */
        foreach ($optionsValues as $optionValue) {
            $arrayOptions[$optionValue->getOptionCode()] = $optionValue->getCode();
        }

        $matching = $this->productVariantRepository->findByOptions($product->getCode(), $arrayOptions);

        return !empty($matching);
    }

    private function clearOtherCombinations(BundleGeneratorInterface $generator, array $combinations): void
    {
        foreach ($generator->getCombinations() as $combination) {
            if (!in_array($combination, $combinations, true)) {
                $generator->removeCombination($combination);
            }
        }
    }
}
