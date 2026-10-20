<?php

namespace App\Factory\ReturnProduct;

use App\Doctrine\Entity\ProductVariantEcotax;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use App\Provider\ReturnProduct\ReturnProductComboProviderInterface;
use App\Provider\ReturnProduct\ReturnProductProvider;
use App\Provider\ReturnProduct\ReturnProductVariantEcotaxProviderInterface;
use App\Provider\ReturnProduct\ReturnProductVariantProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Factory\ProductFactory;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;

class ReturnProductFactory extends ProductFactory implements ReturnFactoryInterface
{
    public ReturnProductProvider $productReturnProvider;
    private array $updatedProducts = [];

    public function __construct(
        ProductFactoryInterface $factory,
        ProductVariantFactoryInterface $variantFactory,
        private readonly TaxonFactoryInterface $taxonFactory,
        private readonly RepositoryInterface $taxonRepository,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly ReturnProductVariantProviderInterface $variantReturnProvider,
        public ReturnProductComboProviderInterface $returnProductComboProvider,
        public ReturnProductVariantEcotaxProviderInterface $returnProductVariantEcotaxProvider,
        public ProductVariantRepository $variantRepository,
        public EntityManagerInterface $taxonManager
    ) {
        parent::__construct($factory, $variantFactory);
    }

    /**
     * @param ProductInterface $originalProduct
     */
    public function createNewFromExisting($originalProduct): ProductInterface
    {
        $product = $this->createNew();
        $product->setCode(ReturnProductProvider::PRODUCT_RETURN_PREFIX . $originalProduct->getCode());
        $product->setName(ucfirst((string) $product->getCode()));
        $product->setSlug($product->getCode());
        $product->setEnabled(false);
        $product->setWeighting($originalProduct->getWeighting());

        /** @var TaxonInterface $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $product->getCode()]);
        if (null !== $taxon) {
            $taxon = $this->taxonFactory->createNew();
            $taxon->setSlug($product->getSlug());
            $taxon->setName($product->getName());
            $taxon->setCode($product->getCode());
            $this->taxonManager->persist($taxon);
        }

        $channel = $this->channelRepository->findOneByCode('return');
        $product->addChannel($channel);
        $product->setMainTaxon($taxon);

        return $product;
    }

    /**
     * @param ProductInterface $originalProduct
     * @param ProductInterface $product
     */
    public function updateFromExisting($originalProduct, $product): void
    {
        // Prevent multiple cascade update of the same Product for perfs
        if (!in_array($product, $this->updatedProducts, true)) {
            $this->updatedProducts[] = $product;
            $this->updateVariants($originalProduct, $product);
        }
    }

    private function updateVariants(ProductInterface $originalProduct, ProductInterface $product): void
    {
        foreach ($originalProduct->getVariants() as $originalVariant) {
            /** @var ProductVariantInterface $variant */
            $variant = $this->variantReturnProvider->provide($originalVariant, $product);

            if (!$product->hasVariant($variant)) {
                $product->addVariant($variant);
            }

            $this->updateCombosVariants($originalVariant, $product);
            $this->updateProductVariantEcotax($originalVariant, $variant);
        }
    }

    private function updateCombosVariants(ProductVariantInterface $originalVariant, ProductInterface $product): void
    {
        $combos = $this->variantReturnProvider->provideCombosFromVariant($originalVariant);

        /** @var ProductVariantInterface $combo */
        foreach ($combos as $combo) {
            $returnComboProduct = $this->productReturnProvider->provide($combo->getProduct());

            if ($returnComboProduct !== $product) {
                $this->updateFromExisting($combo->getProduct(), $returnComboProduct);
            }

            $this->updateProductCombo($combo, $product);
        }
    }

    private function updateProductCombo(ProductVariantInterface $originalVariantCombo, ProductInterface $product): void
    {
        $codes = explode('_', (string) $originalVariantCombo->getCode());

        foreach ($codes as $code) {
            $originalVariant = $this->variantRepository->findOneByCode($code);

            if (null !== $originalVariant) {
                $variant = $this->variantReturnProvider->provide($originalVariant, $product);
                $this->returnProductComboProvider->provide($originalVariant, $variant);
            }
        }
    }

    private function updateProductVariantEcotax(ProductVariantInterface $originalVariant, ProductVariant $variant): void
    {
        /** @var ProductVariantEcotax $originalProductVariantEcotax */
        foreach ($originalVariant->getProductVariantEcotax() as $originalProductVariantEcotax) {
            $productVariantEcotax = $this->returnProductVariantEcotaxProvider->provide($originalProductVariantEcotax, $variant);

            if (!$variant->getProductVariantEcotax()->contains($productVariantEcotax)) {
                $variant->addProductVariantEcotax($productVariantEcotax);
            }
        }
    }
}
