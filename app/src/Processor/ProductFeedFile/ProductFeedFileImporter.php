<?php

declare(strict_types=1);

namespace App\Processor\ProductFeedFile;

use App\Doctrine\Entity\ProductFeedFileImporter as EntityProductFeedFileImporter;
use App\Message\ProductFeedFile\Importer\FileRow;
use App\Model\ProductFeedFile\Row;
use App\Processor\ProductFeedFile\Exceptions\ProductFeedFileImporterNotFoundException;
use App\Processor\ProductFeedFile\Exceptions\ProductFeedFileValidatorException;
use Doctrine\Common\Annotations\AnnotationReader;
use Psr\Log\LoggerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ProductFeedFileImporter implements ProductFeedFileImporterInterface
{
    private readonly Serializer $serializer;

    public function __construct(
        private readonly ProductFeedFileValidatorInterface $validator,
        private readonly FactoryInterface $productFeedFileImporterFactory,
        private readonly RepositoryInterface $productFeedFileImporterRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $encoders = ['csv' => new CsvEncoder(), 'json' => new JsonEncoder()];
        $normalizers = [
            new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter),
            new ArrayDenormalizer(),
        ];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function import(string $path, string $author): void
    {
        // @var EntityProductFeedFileImporter $productFeedFileImporter
        $productFeedFileImporter = $this->createProductFeedFileInstance($author);
        $fileContent = $this->getFileContent($path);

        // validation processus
        $this->validateFileContent($fileContent, $productFeedFileImporter);

        // save the entity to acquire an id
        $this->productFeedFileImporterRepository->add($productFeedFileImporter);
        $this->readFile($fileContent, $productFeedFileImporter);
        $this->productFeedFileImporterRepository->add($productFeedFileImporter);
    }

    public function finishProcessing(int $productFeedFileImporterId): void
    {
        $productFeedFileImporter = $this->productFeedFileImporterRepository->find($productFeedFileImporterId);
        $this->changeState($productFeedFileImporter, EntityProductFeedFileImporter::STATE_PUBLISHED);
        $this->productFeedFileImporterRepository->add($productFeedFileImporter);
    }

    private function createProductFeedFileInstance(string $author): EntityProductFeedFileImporter
    {
        // @var EntityProductFeedFileImporter $productFeedFile
        $productFeedFile = $this->productFeedFileImporterFactory->createNew();
        $productFeedFile->setAuthor($author);

        return $productFeedFile;
    }

    private function getFileContent(string $path): string
    {
        $file = file_get_contents($path);
        if (false === $file) {
            throw new ProductFeedFileImporterNotFoundException('File not found');
        }

        return $file;
    }

    private function validateFileContent(string $fileContent, EntityProductFeedFileImporter $productFeedFileImporter): void
    {
        $isValidated = true;
        try {
            $this->validator->validate($fileContent);
        } catch (ProductFeedFileValidatorException $e) {
            $this->logger->error($e->getMessage());
            $isValidated = false;
        } finally {
            $productFeedFileImporter->setValidated($isValidated);
        }
    }

    private function readFile(string $fileContent, EntityProductFeedFileImporter $productFeedFileImporter, string $fileType = 'csv'): void
    {
        // first we need to be sure we can decode the file
        $output = $this->serializer->deserialize($fileContent, Row::class . '[]', $fileType);
        $fileId = $productFeedFileImporter->getId();

        $lastKey = array_key_last($output);
        $this->changeState($productFeedFileImporter, EntityProductFeedFileImporter::STATE_PROCESSING);
        foreach ($output as $key => $line) {
            $this->messageBus->dispatch(new FileRow($line, $fileId, $key === $lastKey));
        }
    }

    private function changeState(EntityProductFeedFileImporter $productFeedFileImporter, string $newState): void
    {
        $productFeedFileImporter->setState($newState);
        $this->productFeedFileImporterRepository->add($productFeedFileImporter);
    }
}
