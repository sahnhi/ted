<?php

namespace App\Factory\View;

use App\View\ImageView;
use Sylius\Component\Core\Model\ImageInterface;

final class ImageViewFactory implements ImageViewFactoryInterface
{
    public function create(ImageInterface $image): ImageView
    {
        $view = new ImageView();
        $view->code = $image->getType();
        $view->path = $image->getPath();

        return $view;
    }
}
