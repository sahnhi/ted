<?php

namespace App\Menu;

use App\Doctrine\Entity\Sylius\ProductInterface;
use Knp\Menu\ItemInterface;
use Sylius\Bundle\AdminBundle\Event\ProductMenuBuilderEvent;

final class AdminProductShowMenuListener
{
    public function addAdminProductShowMenuItems(ProductMenuBuilderEvent $event): ItemInterface
    {
        $menu = $event->getMenu();
        /**
         * @var ProductInterface $product
         */
        $product = $event->getProduct();

        $menu->getChild('manage_variants')->setAttribute('type', 'dropdown');

        foreach ($menu->getChild('manage_variants')->getChildren() as $child) {
            $child->setAttribute('class', 'item');
        }

        if (null !== $product->getId()) {
            $menu->addChild(
                'generate_returns',
                [
                    'route' => 'sylius_admin_product_return_generation',
                    'routeParameters' => [
                        'id' => $product->getId(),
                    ],
                ]
            )
                ->setAttribute('confirmation', true)
                ->setLabel('app.form.product.generate_returns')
                ->setLabelAttribute('icon', 'random')
                ->setLabelAttribute('color', 'orange')
                ->setLabelAttribute('confirmation', true);

            if ($product->isEmptyOrContainsBundle()) {
                $menu->addChild(
                    'generate_bundles',
                    [
                        'route' => 'app_admin_bundle_generation_form',
                        'routeParameters' => [
                            'productId' => $product->getId(),
                        ],
                    ]
                )
                    ->setLabel('app.ui.bundle_association.labels.generate')
                    ->setLabelAttribute('icon', 'plus')
                    ->setLabelAttribute('color', 'green');

                $menu->getChild('manage_variants')
                    ->removeChild('product_variant_create')
                    ->removeChild('product_variant_generate');
            }
        }

        return $menu;
    }
}
