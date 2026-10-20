<?php

declare(strict_types=1);

namespace App\MessageHandler\ProductFeedFile\Importer;

use App\Doctrine\Entity\ProductFeedFile;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Message\ProductFeedFile\Importer\FileRow;
use App\Model\ProductFeedFile\Row;
use App\Processor\ProductFeedFile\ProductFeedFileImporter;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class FileRowHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $productVariantRepository,
        private readonly FactoryInterface $productFeedFileFactory,
        private readonly RepositoryInterface $productFeedFileRepository,
        private readonly ProductFeedFileImporter $productFeedFileImporter,
    ) {
    }

    public function __invoke(FileRow $fileRow): void
    {
        // @var Row $rowData
        $rowData = $fileRow->getRowData();
        $productVariant = $this->getProductVariant($rowData);
        if (null === $productVariant) {
            return;
        }
        $productFeedFile = $this->createProductFeedFileRow($productVariant, $rowData);

        $this->addTags($rowData, $productFeedFile);
        $this->addAdditionalImages($rowData, $productFeedFile);
        $this->productFeedFileRepository->add($productFeedFile);
        $this->setVariantGtin($rowData, $productVariant);

        if ($this->canFinishProcessing($fileRow)) {
            $this->productFeedFileImporter->finishProcessing($fileRow->getProductFeedId());
        }
    }

    private function createProductFeedFileRow(ProductVariant $productVariant, Row $rowData): ProductFeedFile
    {
        $productFeedFile = $productVariant->getProductFeedFile();

        if (is_null($productFeedFile)) {
            $productFeedFile = $this->productFeedFileFactory->createNew();
            $productVariant->setProductFeedFile($productFeedFile);
        }

        $productFeedFile
            ->setGoogleId($rowData->getId())
            ->setProductVariant($productVariant)
            ->setTitle($rowData->getTitle())
            ->setDescription($rowData->getDescription())
            ->setUrl($rowData->getLink())
            ->setObjectState($rowData->getState())
            ->setPromotionIdentifier($rowData->getPromotionCode())
            ->setGroupIdentifier($rowData->getGroupReference())
            ->setGoogleCategory($rowData->getGoogleProductCategory())
            ->setProductType($rowData->getProductType())
            ->setMaterial($rowData->getMaterial())
            ->setSizingSystem($rowData->getSizingPattern())
            ->setComposition($rowData->getMaterial())
            ->setMainImage($rowData->getMainImageLink())
            ->setColor($rowData->getColor())
            ->setHighlight($rowData->getHighlight());

        return $productFeedFile;
    }

    private function addTags(Row $rowData, ProductFeedFile $productFeedFile): void
    {
        $productFeedFile->clearTags();
        foreach ($rowData->getCustomTag() as $tag) {
            $productFeedFile->setTags($tag);
        }
    }

    private function addAdditionalImages(Row $rowData, ProductFeedFile $productFeedFile): void
    {
        $productFeedFile->clearAdditionalImages();
        foreach ($rowData->getAdditionalImageLink() as $additionalImage) {
            $productFeedFile->addAdditionalImage($additionalImage);
        }
    }

    private function canFinishProcessing(FileRow $row): bool
    {
        return $row->isLastRow();
    }

    private function getProductVariant(Row $rowData): ?ProductVariant
    {
        return $this->productVariantRepository->findOneBy(['code' => $rowData->getFactoryReference(), 'enabled' => true]);
    }

    private function setVariantGtin(Row $rowData, ProductVariant $variant): void
    {
        if (is_null($variant->getEan13())) {
            $variant->setEan13((int) $rowData->getGtin());
            $this->productVariantRepository->add($variant);
        }
    }
}
