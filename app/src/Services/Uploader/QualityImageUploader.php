<?php

namespace App\Services\Uploader;

use App\Generator\QualityImagePathGenerator;
use Gaufrette\Filesystem;
use Sylius\Component\Core\Uploader\ImageUploader;

class QualityImageUploader extends ImageUploader
{
    public function __construct(
        Filesystem $filesystem,
        ?QualityImagePathGenerator $imagePathGenerator = null
    ) {
        parent::__construct($filesystem, $imagePathGenerator);
    }
}
