<?php

declare(strict_types=1);

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Repository\AssociationLazyHydrator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductTaxonRepository as BaseProductTaxonRepository;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;

final class ProductTaxonRepository extends BaseProductTaxonRepository implements ProductTaxonRepositoryInterface
{
    private readonly AssociationLazyHydrator $associationLazyHydrator;

    public function __construct(EntityManager $entityManager, ClassMetadata $class)
    {
        parent::__construct($entityManager, $class);

        $this->associationLazyHydrator = new AssociationLazyHydrator($entityManager, $class);
    }

    public function getFullProducts(string $locale, ?array $slugs): array
    {
        $productTaxOns = $this->findBy([], ['position' => 'ASC']);

        $this->associationLazyHydrator
            ->hydrateAssociations($productTaxOns, [
                'product',
                'product.translations',
                'product.variants',
                'product.variants.translations',
                'product.variants.channelPricings',
                'product.variants.packs',
                'product.options',
                'product.options.defaultOptionsValue',
                'product.options.defaultOptionsValue.locale',
                'product.options.defaultOptionsValue.optionValue',
                'product.options.translations',
                'product.options.values',
                'product.options.values.translations',
            ]);

        return $productTaxOns;
    }

    public function findAllByTaxon(TaxonInterface $taxon): array
    {
        $productTaxOns = $this->findBy(['taxon' => $taxon], ['position' => 'ASC']);

        $this->associationLazyHydrator
            ->hydrateAssociations($productTaxOns, [
                'product',
                'product.productGoogleRss',
                'product.translations',
                'product.attributes',
                'product.images',
                'product.mainTaxon',
                'product.variants',
                'product.variants.translations',
                'product.variants.channelPricings',
                'product.variants.packs',
                'product.variants.bundleAssociations',
                'product.variants.bundleAssociations.variant',
                'product.variants.bundleAssociations.variant.translations',
                'product.variants.bundleAssociations.variant.product',
                'product.variants.bundleAssociations.variant.optionValues',
                'product.variants.bundleAssociations.variant.optionValues.option',
                'product.variants.bundleAssociations.prices',
                'product.variants.optionValues',
                'product.variants.optionValues.translations',
                'product.variants.optionValues.option',
                'product.variants.optionValues.option.translations',
                'product.options',
                'product.options.defaultOptionsValue',
                'product.options.defaultOptionsValue.product',
                'product.options.defaultOptionsValue.locale',
                'product.options.defaultOptionsValue.optionValue',
                'product.options.translations',
                'product.options.values',
                'product.options.values.translations',
            ]);

        return $productTaxOns;
    }

    public function getByTaxon(TaxonInterface $taxon, array $productTaxons): array
    {
        return array_filter($productTaxons, fn ($value): bool => $value->getTaxon()->getCode() === $taxon->getCode());
    }

    public function getOneByProductId(int $id, array $productTaxons): ?ProductTaxonInterface
    {
        $result = current(array_filter($productTaxons, fn ($value): bool => $value->getProduct()->getId() === $id));

        if (!$result) {
            return null;
        } else {
            return $result;
        }
    }
}
