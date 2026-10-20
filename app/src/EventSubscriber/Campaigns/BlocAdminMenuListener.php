<?php

declare(strict_types=1);

namespace App\EventSubscriber\Campaigns;

use App\Doctrine\Entity\Campaigns\Bloc;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class BlocAdminMenuListener extends MenuBuilderEvent
{
    public function __construct(
        FactoryInterface $factory,
        ItemInterface $menu,
        private readonly Bloc $bloc,
    ) {
        parent::__construct($factory, $menu);
    }

    public function getBloc(): Bloc
    {
        return $this->bloc;
    }
}
