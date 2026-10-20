<?php

declare(strict_types=1);

namespace App\Menu\Campaigns;

use App\EventSubscriber\Campaigns\BlocAdminMenuListener;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class BlocMenuListener
{
    public const EVENT_NAME = 'sylius.menu.admin.campaign_bloc.form';

    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createMenu(array $options = []): ItemInterface
    {
        $blocMenu = $this->factory->createItem('root');
        $blocMenu
            ->addChild('main-information')
            ->setAttribute('template', '@SyliusAdmin/CampaignBloc/Tab/_main_information.html.twig')
            ->setLabel('app.ui.bloc_campaign.menu.page_menu.main_information');

        $blocMenu
            ->addChild('variants')
            ->setAttribute('template', '@SyliusAdmin/CampaignBloc/Tab/_variants.html.twig')
            ->setLabel('app.ui.bloc_campaign.menu.page_menu.variants');

        $blocMenu
            ->addChild('focus')
            ->setAttribute('template', '@SyliusAdmin/CampaignBloc/Tab/_focus.html.twig')
            ->setLabel('app.ui.bloc_campaign.menu.page_menu.focus');

        $this->eventDispatcher->dispatch(
            new BlocAdminMenuListener($this->factory, $blocMenu, $options['bloc'])
        );

        return $blocMenu;
    }
}
