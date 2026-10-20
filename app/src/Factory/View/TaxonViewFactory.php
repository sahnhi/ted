<?php

namespace App\Factory\View;

use App\Doctrine\Repository\Sylius\ProductReviewRepositoryInterface;
use App\View\ProductView;
use App\View\TaxonView;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

final class TaxonViewFactory implements TaxonViewFactoryInterface
{
    public function __construct(
        private readonly ImageViewFactoryInterface $imageViewFactory,
        private readonly ProductViewFactoryInterface $productViewFactory,
        private readonly ProductReviewRepositoryInterface $productReviewRepository
    ) {
    }

    public function fillTaxonView(TaxonInterface $taxon, TaxonTranslationInterface $taxonTranslation): TaxonView
    {
        $taxonView = new TaxonView();

        $taxonView->code = $taxon->getCode();
        $taxonView->position = $taxon->getPosition();
        $taxonView->name = $taxonTranslation->getName();
        $taxonView->slug = $taxonTranslation->getSlug();
        $taxonView->description = $taxonTranslation->getDescription();

        $stats = $this->productReviewRepository->getAverageRatingsAndCountReviews($taxon->getId());

        $taxonView->averageRating = $stats['averageRating'];
        $taxonView->numberOfRating = $stats['numberOfRating'];

        unset($stats);

        return $taxonView;
    }

    private function getBase(TaxonInterface $taxon, string $locale): TaxonView
    {
        return $this->fillTaxonView($taxon, $taxon->getTranslation($locale));
    }

    private function getBaseForList(TaxonInterface $taxon, string $locale): TaxonView
    {
        return $this->fillTaxonView($taxon, $taxon->getTranslations()->get($locale));
    }

    public function create(TaxonInterface $taxon, array $products, string $locale): TaxonView
    {
        $taxonView = $this->getBase($taxon, $locale);

        foreach ($taxon->getImages() as $image) {
            $taxonView->images[] = $this->imageViewFactory->create($image);
        }

        $taxonView->products = $products;

        return $taxonView;
    }

    public function list(TaxonInterface $taxon, ChannelInterface $channel, array $productTaxons, string $locale, bool $bounds = false): TaxonView
    {
        $taxonView = $this->getBaseForList($taxon, $locale);

        /** @var ?ProductTaxonInterface $productTaxon */
        foreach ($productTaxons as $productTaxon) {
            $taxonView->products[] = $this->productViewFactory->listForTaxon($productTaxon->getProduct(), $productTaxons, $channel, $locale);
        }

        if ($bounds) {
            /** @var ProductView $variant */
            $minBound = 0;
            foreach ($taxonView->products as $product) {
                $minBound += $product->minBound;
            }

            $taxonView->minBound = $minBound;
        }

        return $taxonView;
    }
}
