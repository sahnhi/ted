<?php

namespace App\Generator;

use Sylius\Component\Core\Generator\ImagePathGeneratorInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class QualityImagePathGenerator implements ImagePathGeneratorInterface
{
    public const QUALITY_PATH = 'quality/';

    public function generate(ImageInterface $image): string
    {
        /** @var UploadedFile $file */
        $file = $image->getFile();
        $date = new \DateTimeImmutable();

        return self::QUALITY_PATH
            . $image->getOwner()->getNumber()
            . '/'
            . $date->format('Ymd_U_')
            . basename($file->getClientOriginalName(), $file->getClientOriginalExtension())
            . $file->guessExtension();
    }
}
