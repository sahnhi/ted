<?php

namespace App\Provider\ReturnProduct;

use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use App\Factory\ReturnProduct\ReturnProductVariantFactory;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Product\Model\ProductVariantTranslation;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnProductVariantProvider implements ReturnProductVariantProviderInterface
{
    public const PRODUCT_VARIANT_RETURN_PREFIX = 'R';
    public const PRODUCT_VARIANT_RETURN_TRANSLATION = 'Retour ';

    public function __construct(
        private readonly ProductVariantRepository $variantRepository,
        private readonly ReturnProductVariantFactory $returnVariantFactory,
        private readonly RepositoryInterface $channelPricingRepository,
        private readonly RepositoryInterface $variantTranslationRepository,
        private readonly LocaleContextInterface $locale,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly FactoryInterface $channelPricingFactory,
    ) {
    }

    public function provide($originalVariant, $product): ProductVariantInterface
    {
        /** @var ProductVariantInterface $variant */
        $variant = $this->variantRepository->findOneByCode(self::PRODUCT_VARIANT_RETURN_PREFIX . $originalVariant->getCode());

        if (null === $variant) {
            $variant = $this->provideNew($originalVariant, $product);
        }

        return $variant;
    }

    public function provideNew($originalVariant, $product): ProductVariantInterface
    {
        $variant = $this->returnVariantFactory->createNewFromExisting($originalVariant);
        $variant->setProduct($product);

        $translation = new ProductVariantTranslation();
        $translation->setName(self::PRODUCT_VARIANT_RETURN_TRANSLATION . $originalVariant->getName());
        $translation->setLocale($this->locale->getLocaleCode());
        $translation->setTranslatable($variant);
        $variant->addTranslation($translation);

        $this->variantRepository->add($variant);
        $this->variantTranslationRepository->add($translation);

        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            /** @var ChannelPricingInterface $channelPricing */
            $channelPricing = $this->channelPricingFactory->createNew();
            $channelPricing->setProductVariant($variant);
            $channelPricing->setChannelCode($channel->getCode());
            $channelPricing->setPrice(0);

            $this->channelPricingRepository->add($channelPricing);
        }

        return $variant;
    }

    public function provideCombosFromVariant(ProductVariantInterface $originalVariant, bool $return = false): array
    {
        $code = $return ? self::PRODUCT_VARIANT_RETURN_PREFIX . $originalVariant->getCode() : $originalVariant->getCode();

        return $this->clearCombos($this->variantRepository->findComboCodesByCode($code), $return, $originalVariant);
    }

    private function clearCombos(array $variantCombos, bool $return, ProductVariantInterface $variant): array
    {
        /**
         * @var                         $key
         * @var ProductVariantInterface $variantCombo
         */
        foreach ($variantCombos as $key => $variantCombo) {
            $isReturn = str_starts_with((string) $variantCombo->getCode(), self::PRODUCT_VARIANT_RETURN_PREFIX);
            if ($isReturn !== $return || $variant->getId() === $variantCombo) {
                unset($variantCombos[$key]);
            }
        }

        return $variantCombos;
    }
}
