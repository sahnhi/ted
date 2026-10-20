<?php

namespace App\Controller\Admin\Quality;

use App\Doctrine\Entity\QualityImage;
use App\Services\Filesystem\GoogleStorageQualityImageFilesystem;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class DownloadImageAction
{
    public function __construct(
        private readonly EntityRepository $qualityImageRepository,
        private readonly GoogleStorageQualityImageFilesystem $googleStorageFilesystem
    ) {
    }

    public function __invoke(Request $request, string $id, string $image): Response
    {
        /** @var QualityImage|null $qualityImage */
        $qualityImage = $this->qualityImageRepository->findOneBy(['id' => $image]);
        Assert::notNull($qualityImage);

        $imageFile = $this->googleStorageFilesystem->retrieve($qualityImage->getPath());

        $response = new Response(
            $imageFile->getContent(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/force-download']
        );
        $response->headers->add([
            'Content-Disposition' => 'attachment; filename="' . str_replace('/', '_', (string) $imageFile->getName()) . '"',
        ]);
        $response->setContent($imageFile->getContent());

        return $response;
    }
}
