<?php

namespace App\Menu;

use Sylius\Bundle\AdminBundle\Event\PromotionMenuBuilderEvent;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminPromotionShowMenuListener
{
    public function __construct(
        protected AuthorizationCheckerInterface $authChecker
    ) {
    }

    public function addAdminPromotionShowMenuItems(PromotionMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $promotion = $event->getPromotion();

        if (null !== $promotion->getId() && $this->authChecker->isGranted(AdminUserInterface::DEFAULT_ADMIN_ROLE)) {
            $menu->addChild('duplicate', [
                'route' => 'sylius_admin_promotion_create',
                'routeParameters' => ['duplicateId' => $promotion->getId()],
            ])
                ->setLabel('sylius.ui.duplicate')
                ->setLabelAttribute('icon', 'copy')
                ->setLabelAttribute('color', 'blue');
        }
    }
}
