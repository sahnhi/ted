<?php

declare(strict_types=1);

namespace App\Channable\Exporter;

use Doctrine\Common\Annotations\AnnotationReader;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class FileStreamExporter
{
    private readonly Serializer $serializer;

    public function __construct(
        private readonly RepositoryInterface $channableFileLogRepository,
        private readonly RepositoryInterface $channableFileRowRepository,
    ) {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $encoders = [new CsvEncoder()];

        $normalizers = [new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function export(int $id): string
    {
        $channableFileLog = $this->channableFileLogRepository->find($id);
        $data = $this->channableFileRowRepository->findBy(['channableFileLog' => $channableFileLog]);
        $rows = $this->filterRows($data);

        return $this->serializer->serialize($rows, 'csv', ['groups' => 'channable']);
    }

    private function filterRows(iterable $data): iterable
    {
        foreach ($data as $row) {
            if ($row->getVariant()->isEnabled()) {
                yield $row;
            }
        }
    }
}
