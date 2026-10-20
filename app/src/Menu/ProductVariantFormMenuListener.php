<?php

namespace App\Menu;

use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Sylius\Bundle\AdminBundle\Event\ProductVariantMenuBuilderEvent;

class ProductVariantFormMenuListener
{
    public function addItems(ProductVariantMenuBuilderEvent $event): void
    {
        /**
         * @var ProductVariantInterface $variant
         */
        $variant = $event->getProductVariant();
        $menu = $event->getMenu();

        if ($variant->isBundle()) {
            $menu
                ->addChild('bundle')
                ->setAttribute('template', 'Admin/ProductVariant/Tab/_bundle.html.twig')
                ->setLabel('app.ui.bundle');
        }

        $menu->addChild('google-rss')
            ->setAttribute('template', '@SyliusAdmin/Product/Tab/_googleRss.html.twig')
            ->setLabel('sylius.ui.products.google_rss')
            ->setCurrent(false);

        $menu->addChild('google_merchant_center_feed_file')
            ->setAttribute('template', '@SyliusAdmin/Product/Tab/_googleRss.html.twig')
            ->setLabel('sylius.ui.products.google_merchant_center_feed_file')
            ->setCurrent(false);
    }
}
