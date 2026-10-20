<?php

declare(strict_types=1);

namespace App\EventSubscriber\Campaigns;

use App\Doctrine\Entity\Campaigns\Campaign;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class CampaignAdminMenuListener extends MenuBuilderEvent
{
    public function __construct(
        FactoryInterface $factory,
        ItemInterface $menu,
        private readonly Campaign $campaign,
    ) {
        parent::__construct($factory, $menu);
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }
}
