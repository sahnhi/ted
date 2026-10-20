<?php

namespace App\Menu;

use App\Model\Role;
use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminMenuListener
{
    public function __construct(
        protected AuthorizationCheckerInterface $authChecker
    ) {
    }

    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $salesMenu = $menu->getChild('sales');
        $salesMenu
            ->addChild('order_store', ['route' => 'sylius_admin_order_store_index'])
            ->setLabel('sylius.ui.order_stores')
            ->setLabelAttribute('icon', 'icon paste');

        $salesMenu
            ->addChild(
                'out_of_stock',
                [
                    'route' => 'sylius_admin_order_outofstock_index',
                    'routeParameters' => ['criteria[state_out_of_stock]' => 'out_of_stock'],
                ]
            )->setLabel('sylius.ui.order_outofstocks')
            ->setLabelAttribute('icon', 'icon history');

        $salesMenu
            ->addChild('order_return', ['route' => 'sylius_admin_order_return_index'])
            ->setLabel('sylius.ui.order_returns')
            ->setLabelAttribute('icon', 'icon reply');

        $salesMenu
            ->addChild('order_return_log', ['route' => 'app_admin_order_return_logistic_index'])
            ->setLabel('app.ui.order_return_logistics')
            ->setLabelAttribute('icon', 'icon reply');

        $salesMenu->addChild(
            'quality',
            [
                'route' => 'app_admin_quality_index',
            ]
        )->setLabel('app.ui.quality.menu')
            ->setLabelAttribute('icon', 'icon check');
        $salesMenu->addChild(
            'order_payments',
            [
                'route' => 'app_admin_order_payments_index',
            ]
        )->setLabel('app.ui.order_payments_menu')
            ->setLabelAttribute('icon', 'icon frown');

        $salesMenu->reorderChildren(
            [
                'orders',
                'payments',
                'out_of_stock',
                'order_return',
                'order_return_log',
                'order_store',
                'shipments',
                'quality',
                'order_payments',
            ]
        );

        $marketingMenu = $menu->getChild('marketing');
        $marketingMenu
            ->addChild('coupon', ['route' => 'sylius_admin_coupon_index'])
            ->setLabel('sylius.menu.admin.coupons')
            ->setLabelAttribute('icon', 'cart plus');
        $marketingMenu
            ->addChild('promotion_category', ['route' => 'app_admin_promotion_category_index'])
            ->setLabel('sylius.menu.admin.promotion_caegories')
            ->setLabelAttribute('icon', 'address book');

        $marketingMenu
            ->addChild('rtbs', ['route' => 'app_admin_rtb_infos_index'])
            ->setLabel('app.ui.rtb.menu')
            ->setLabelAttribute('icon', 'icon bullhorn');

        if ($this->authChecker->isGranted(Role::ROLE_ADMIN)) {
            $catalogMenu = $menu->getChild('catalog');

            $catalogMenu
                ->addChild('bundles', ['route' => 'app_admin_bundle_index'])
                ->setLabel('sylius.menu.admin.bundles')
                ->setLabelAttribute('icon', 'cube');

            $catalogMenu
                ->addChild('pack', ['route' => 'sylius_admin_pack_index'])
                ->setLabel('sylius.menu.admin.packs')
                ->setLabelAttribute('icon', 'cubes');

            $catalogMenu
                ->addChild('product_options_values_locales_default', ['route' => 'sylius_admin_product_option_value_locale_default_index'])
                ->setLabel('sylius.menu.admin.product_options_values_locales_default')
                ->setLabelAttribute('icon', 'outdent');
            $catalogMenu->addChild('ecotax', ['route' => 'sylius_admin_ecotax_index'])
                ->setLabel('sylius.menu.admin.ecotaxes')
                ->setLabelAttribute('icon', 'recycle');
            $catalogMenu
                ->addChild('product_combos_label', ['route' => 'sylius_admin_product_combo_index'])
                ->setLabel('sylius.menu.admin.product_combos_label')
                ->setLabelAttribute('icon', 'boxes');
            $catalogMenu->getChild('attributes')
                ->setLabelAttribute('icon', 'list ul');
            $catalogMenu
                ->addChild('product_cms_sizing_guide', ['route' => 'app_admin_product_cms_sizing_guide_index'])
                ->setLabel('sylius.menu.admin.product_cms_sizing_guide')
                ->setLabelAttribute('icon', 'sort');

            $customerMenu = $menu->getChild('customers');
            $customerMenu
                ->addChild('familiarity', ['route' => 'sylius_admin_familiarity_index'])
                ->setLabel('sylius.menu.admin.familiarities')
                ->setLabelAttribute('icon', 'tv');

            $marketingMenu = $menu->getChild('marketing');
            $marketingMenu
                ->addChild('product_review_question', ['route' => 'sylius_admin_product_review_question_index'])
                ->setLabel('sylius.menu.admin.product_review_question.index')
                ->setLabelAttribute('icon', 'question');
            $marketingMenu
                ->addChild('product_review_answer_choices', ['route' => 'sylius_admin_product_review_answer_choices_index'])
                ->setLabel('sylius.menu.admin.product_review_answer_choices.index')
                ->setLabelAttribute('icon', 'question circle');
            $marketingMenu
                ->addChild('app_admin_ask_review', ['route' => 'app_admin_ask_review_template_index'])
                ->setLabel('sylius.menu.admin.app_admin_ask_review.index')
                ->setLabelAttribute('icon', 'newspaper outline');
            $marketingMenu
                ->addChild('app_admin_campaigns', ['route' => 'app_admin_campaign_index'])
                ->setLabel('app.ui.campaign.menu.admin_menu')
                ->setLabelAttribute('icon', 'bullhorn');
            $marketingMenu
                ->addChild('app_admin_campaign_bloc', ['route' => 'app_admin_campaign_bloc_index'])
                ->setLabel('app.ui.bloc_campaign.menu.main_menu_title')
                ->setLabelAttribute('icon', 'cube');
            $marketingMenu
                ->addChild('app_admin_product_feed_file', ['route' => 'app_admin_product_feed_file_index'])
                ->setLabel('app.ui.product_feed_file.product_admin_menu')
                ->setLabelAttribute('icon', 'google outline');
            $marketingMenu
                ->addChild('app_admin_satellite_page', ['route' => 'app_admin_satellite_page_index'])
                ->setLabel('app.ui.satellite_page.admin_menu')
                ->setLabelAttribute('icon', 'anchor');

            $this->addTransportMenu($menu);
            $this->addRetailMenu($menu);
            $this->addExportAndStatMenu($menu);
            $this->handleReasonMenu($menu);
            $this->addAccountMenu($menu);

            /**
             * @var ItemInterface $configMenu
             */
            $configMenu = $menu->getChild('configuration');
            $configMenu
                ->addChild('order_return_status', ['route' => 'sylius_admin_order_return_status_index'])
                ->setLabel('sylius.ui.order_return_status')
                ->setLabelAttribute('icon', 'icon reply');

            if (!$this->authChecker->isGranted(AdminUserInterface::DEFAULT_ADMIN_ROLE)) {
                $configMenu->removeChild('admin_users');
            }

            return;
        }

        if ($this->authChecker->isGranted(Role::ROLE_MANAGER)) {
            $menu->removeChild('configuration');
            $menu->removeChild('catalog');
            $menu->removeChild('tax_rates');

            return;
        }

        if ($this->authChecker->isGranted(Role::ROLE_VIEWER)) {
            $menu->removeChild('configuration');
            $menu->removeChild('catalog');
            $menu->removeChild('tax_rates');
            $menu->removeChild('customers');
            $menu->removeChild('marketing');
        }

        if ($this->authChecker->isGranted(Role::ROLE_ACCOUNT)) {
            $menu->removeChild('configuration');
            $menu->removeChild('catalog');
            $menu->removeChild('tax_rates');
            $menu->removeChild('customers');
            $menu->removeChild('marketing');
            $this->addAccountMenu($menu);

            return;
        }
    }

    private function handleReasonMenu(ItemInterface $menu): void
    {
        $configMenu = $menu
            ->addChild('reasons')
            ->setLabel('sylius.menu.admin.reasons');
        $configMenu
            ->addChild('reason', ['route' => 'sylius_admin_reason_index'])
            ->setLabel('sylius.menu.admin.reason.index')
            ->setLabelAttribute('icon', 'icon list ul');
        $configMenu
            ->addChild('reason-group', ['route' => 'sylius_admin_reason_group_index'])
            ->setLabel('sylius.menu.admin.reason_group.index')
            ->setLabelAttribute('icon', 'icon th list');
    }

    private function addExportAndStatMenu(ItemInterface $menu): void
    {
        $exportAndStatMenu = $menu
            ->addChild('exports-stats')
            ->setLabel('Export et statistiques');
        $exportAndStatMenu
            ->addChild('exports', ['route' => 'export_index'])
            ->setLabel('Exports')
            ->setLabelAttribute('icon', 'file alternate');
        $exportAndStatMenu
            ->addChild('statistiques')
            ->setLabel('Statistiques')
            ->setLabelAttribute('icon', 'chart bar');
    }

    private function addRetailMenu(ItemInterface $menu): void
    {
        $retailMenu = $menu
            ->addChild('retail')
            ->setLabel('Retail');
        $retailMenu
            ->addChild('store', ['route' => 'sylius_admin_store_index'])
            ->setLabel('Boutiques')
            ->setLabelAttribute('icon', 'shopping bag');
        $retailMenu
            ->addChild('seller', ['route' => 'sylius_admin_seller_index'])
            ->setLabel('Vendeurs')
            ->setLabelAttribute('icon', 'handshake outline');
        $retailMenu
            ->addChild('commission', ['route' => 'sylius_admin_commission_index'])
            ->setLabel('Commissions')
            ->setLabelAttribute('icon', 'money bill');
    }

    public function addTransportMenu(ItemInterface $menu): void
    {
        $transportMenu = $menu
            ->addChild('transport')
            ->setLabel('sylius.menu.admin.transport');

        $shippingMethod = $menu->getChild('configuration')->getChild('shipping_methods');
        $menu->getChild('configuration')->removeChild('shipping_methods');
        $transportMenu->addChild($shippingMethod);

        $shippingCategories = $menu->getChild('configuration')->getChild('shipping_categories');
        $menu->getChild('configuration')->removeChild('shipping_categories');
        $transportMenu->addChild($shippingCategories);

        $transportMenu
            ->addChild('exceptional_schedule', ['route' => 'sylius_admin_exceptional_schedule_index'])
            ->setLabel('sylius.menu.admin.exceptional_schedule')
            ->setLabelAttribute('icon', 'icon calendar alternate outline');

        $transportMenu
            ->addChild('shipping_method_groups', ['route' => 'app_admin_shipping_method_group_index'])
            ->setLabel('app.ui.shipping_method_groups.admin_menu')
            ->setLabelAttribute('icon', 'warehouse');

        $transportMenu
            ->addChild('warehouse', ['route' => 'sylius_admin_warehouse_index'])
            ->setLabel('sylius.menu.admin.warehouse')
            ->setLabelAttribute('icon', 'icon warehouse');

        $transportMenu
            ->addChild('service_rules', ['route' => 'sylius_admin_service_rules_index'])
            ->setLabel('sylius.menu.admin.service_rules')
            ->setLabelAttribute('icon', 'clipboard list');

        $transportMenu
            ->addChild('zip_code', ['route' => 'sylius_admin_zip_code_index'])
            ->setLabel('sylius.menu.admin.zip_codes')
            ->setLabelAttribute('icon', 'icon address book');

        $transportMenu
            ->addChild('shipping_method_category', ['route' => 'app_admin_shipping_method_category_index'])
            ->setLabel('app.ui.shipping_method_category.menu')
            ->setLabelAttribute('icon', 'icon warehouse');
    }

    private function addAccountMenu(ItemInterface $menu): ItemInterface
    {
        $accountMenu = $menu
            ->addChild('account')
            ->setLabel('app.ui.export_accounting_data.menu');

        $accountMenu
            ->addChild('export_by_delivery_date', ['route' => 'app_admin_export_accounting_data_index'])
            ->setLabel('app.ui.export_accounting_data.export')
            ->setLabelAttribute('icon', 'file alternate');

        return $accountMenu;
    }
}
