<?php

declare(strict_types=1);

namespace App\Channable\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class ChannableMenuListener
{
    public function addChannableMenuItems(MenuBuilderEvent $event): void
    {
        $marketingMenu = $event->getMenu()->getChild('marketing');
        if (null === $marketingMenu) {
            return;
        }

        $marketingMenu
            ->addChild('channable', ['route' => 'app_admin_channable_file_log_index'])
            ->setLabel('Channable')
            ->setLabelAttribute('icon', 'icon wifi');
    }
}
