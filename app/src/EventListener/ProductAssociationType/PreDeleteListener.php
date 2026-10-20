<?php

declare(strict_types=1);

namespace App\EventListener\ProductAssociationType;

use App\Doctrine\Entity\Sylius\ProductAssociationTypeInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Product\Model\ProductAssociationType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class PreDeleteListener
{
    private const PROTECTED_ASSOCIATION_TYPE_CODE = [
        ProductAssociationTypeInterface::TYPE_CART_CROSS_SELLING,
        ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING,
    ];

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(ResourceControllerEvent $event): void
    {
        /** @var ProductAssociationType $productAssociationType */
        $productAssociationType = $event->getSubject();

        Assert::isInstanceOf($productAssociationType, ProductAssociationType::class);

        if (in_array($productAssociationType->getCode(), self::PROTECTED_ASSOCIATION_TYPE_CODE, true)) {
            $event->stop(
                $this->translator->trans('app.form.product_association_type.delete_cross_selling'),
                parameters: ['%type%' => $productAssociationType->getCode()]
            );
        }
    }
}
