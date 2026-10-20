<?php

namespace App\Menu;

use Sylius\Bundle\AdminBundle\Event\ProductMenuBuilderEvent;

class ProductFormMenuListener
{
    public function addAdminProductFormItem(ProductMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu->addChild('google-rss')
            ->setAttribute('template', '@SyliusAdmin/Product/Tab/_googleRss.html.twig')
            ->setLabel('sylius.ui.products.google_rss')
            ->setCurrent(false);

        $this->addCMSMenuItem($event);
        $this->addCMSTechnicalInfos($event);
        $this->addCMSFocus($event);
        $this->addCMSFaq($event);
    }

    private function addCMSMenuItem(ProductMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu
            ->addChild('cms')
            ->setAttribute('template', '@SyliusAdmin/Product/Tab/CMS/cms.html.twig')
            ->setLabel('app.form.product.cms.title');
    }

    private function addCMSTechnicalInfos(ProductMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu
            ->addChild('technical-infos')
            ->setAttribute('template', '@SyliusAdmin/Product/Tab/CMSTechnicalInfos/technical_infos.html.twig')
            ->setLabel('app.form.product.cms.technical_infos.title');
    }

    private function addCMSFocus(ProductMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $menu
            ->addChild('cms-focus')
            ->setAttribute('template', '@SyliusAdmin/Product/Tab/CMSFocus/cms_focus.html.twig')
            ->setLabel('app.form.product.cms.cms_focus.title');
    }

    private function addCMSFaq(ProductMenuBuilderEvent $event): void
    {
        $event
            ->getMenu()
            ->addChild('cms-faq')
            ->setAttribute('template', '@App/Admin/Product/Tab/CMSFaq/cms_faq.html.twig')
            ->setLabel('app.form.product.cms.cms_faq.title');
    }
}
