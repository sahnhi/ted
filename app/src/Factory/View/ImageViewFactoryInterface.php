<?php

namespace App\Factory\View;

use App\View\ImageView;
use Sylius\Component\Core\Model\ImageInterface;

interface ImageViewFactoryInterface
{
    public function create(ImageInterface $image): ImageView;
}
