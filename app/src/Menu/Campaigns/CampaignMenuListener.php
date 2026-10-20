<?php

declare(strict_types=1);

namespace App\Menu\Campaigns;

use App\EventSubscriber\Campaigns\CampaignAdminMenuListener;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CampaignMenuListener
{
    public const EVENT_NAME = 'sylius.menu.admin.campaign.form';

    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createMenu(array $options = []): ItemInterface
    {
        $campaignMenu = $this->factory->createItem('root');
        $campaignMenu
            ->addChild('main-information')
            ->setAttribute('template', '@SyliusAdmin/Campaigns/Tab/_main_information.html.twig')
            ->setLabel('app.ui.campaign.menu.main_information');
        $campaignMenu
            ->addChild('seo')
            ->setAttribute('template', '@SyliusAdmin/Campaigns/Tab/_seo.html.twig')
            ->setLabel('app.ui.campaign.menu.seo');
        $campaignMenu
            ->addChild('blocs')
            ->setAttribute('template', '@SyliusAdmin/Campaigns/Tab/_variants.html.twig')
            ->setLabel('app.ui.campaign.menu.blocs');

        $this->eventDispatcher->dispatch(
            new CampaignAdminMenuListener($this->factory, $campaignMenu, $options['campaign']),
        );

        return $campaignMenu;
    }
}
