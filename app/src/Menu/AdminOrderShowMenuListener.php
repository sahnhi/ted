<?php

namespace App\Menu;

use App\Api\Odoo\Model\Order as OdooOrderInterface;
use App\Controller\Admin\Odoo\Cancel;
use App\Controller\Admin\Odoo\ConfirmReturn;
use App\Controller\Admin\Odoo\Create;
use App\Controller\Admin\Odoo\Destock;
use App\Controller\Admin\Odoo\Ship;
use App\Doctrine\Entity\Sylius\Order;
use App\Model\Role;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Knp\Menu\ItemInterface;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\AdminBundle\Event\OrderShowMenuBuilderEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Model\OrderInterface as OrderInterfaceAlias;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class AdminOrderShowMenuListener
{
    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly FactoryInterface $stateMachineFactory,
    ) {
    }

    public function addAdminOrderShowMenuItems(OrderShowMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $order = $event->getOrder();

        $this->tryAddingCancelButton($menu, $order);

        if (null !== $order->getId()) {
            $menu->setLabel('order-show-menu')
                ->addChild('bis', [
                    'route' => 'sylius_admin_order_creation_bis',
                    'routeParameters' => ['id' => $order->getId()],
                ])
                ->setLabel('app.ui.bis')
                ->setLabelAttribute('icon', 'cart')
                ->setLabelAttribute('color', 'orange');
            $this->addReturnButton($menu, $order);
            $this->addQualityButton($menu, $order);
            $this->addGenerateOrderItemLogisticButton($menu, $order);
            $this->addOdooButton($menu, $order);
        }
    }

    private function addReturnButton(ItemInterface $menu, OrderInterface $order): void
    {
        $returns = $menu
            ->addChild('returns')
            ->setLabel('app.ui.returns')
            ->setAttribute('type', 'select')
            ->setLabelAttribute('icon', 'list')
            ->setLabelAttribute('color', 'olive');

        $returns->addChild('return', [
            'route' => 'sylius_admin_order_creation_return',
            'routeParameters' => [
                'id' => $order->getId(),
            ],
        ])->setLabel('app.ui.return');

        $returns->addChild('exchange', [
            'route' => 'sylius_admin_order_creation_exchange',
            'routeParameters' => [
                'id' => $order->getId(),
            ],
        ])->setLabel('app.ui.exchange');

        $returns->addChild('refund', [
            'route' => 'sylius_admin_order_creation_refund',
            'routeParameters' => [
                'id' => $order->getId(),
            ],
        ])->setLabel('app.ui.refund');

        $returns->addChild('sender', [
            'route' => 'sylius_admin_order_creation_return_sender',
            'routeParameters' => [
                'id' => $order->getId(),
            ],
        ])->setLabel('app.ui.sender');

        $returns->addChild('warehouse', [
            'route' => 'sylius_admin_order_creation_return_warehouse',
            'routeParameters' => [
                'id' => $order->getId(),
            ],
        ])->setLabel('app.ui.exchange_warehouse');

        $returns->addChild(ReturnTypeServiceInterface::RETURN_TYPE_AUTONOMOUS, [
            'route' => 'sylius_admin_order_creation_return_autonomous',
            'routeParameters' => [
                'id' => $order->getId(),
            ],
        ])->setLabel('app.ui.return_autonomous');
    }

    private function tryAddingCancelButton(ItemInterface $menu, OrderInterface $order)
    {
        if ($this->authChecker->isGranted(Role::ROLE_ADMIN)
            && ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode()
            && OrderInterfaceAlias::STATE_CANCELLED !== $order->getState()
        ) {
            $menu
                ->addChild('cancel', [
                    'route' => 'sylius_admin_order_cancel',
                    'routeParameters' => [
                        'id' => $order->getId(),
                        '_csrf_token' => $this->csrfTokenManager->getToken((string) $order->getId())->getValue(),
                    ],
                ])
                ->setAttribute('type', 'transition')
                ->setAttribute('confirmation', true)
                ->setLabel('sylius.ui.cancel')
                ->setLabelAttribute('icon', 'ban')
                ->setLabelAttribute('color', 'yellow');
        }
    }

    private function addQualityButton(ItemInterface $menu, OrderInterface $order)
    {
        $menu
            ->addChild('quality', [
                'route' => 'app_admin_quality_create',
                'routeParameters' => [
                    'orderId' => $order->getId(),
                ],
            ])
            ->setLabel('app.ui.quality.name')
            ->setLabelAttribute('icon', 'check')
            ->setLabelAttribute('color', 'blue');
    }

    private function addGenerateOrderItemLogisticButton(ItemInterface $menu, OrderInterface $order)
    {
        if (0 === count($order->getOrderItemLogistics())
            && OrderPaymentStates::STATE_PAID === $order->getPaymentState()
        ) {
            $menu
                ->addChild('orderItemLogistic', [
                    'route' => 'app_admin_order_item_logistic_generate',
                    'routeParameters' => [
                        'id' => $order->getId(),
                    ],
                ])
                ->setLabel('app.ui.order_item_logistic.generate')
                ->setLabelAttribute('icon', 'box')
                ->setLabelAttribute('color', 'brown');
        }
    }

    /**
     * @param Order&OrderInterface $order
     */
    private function addOdooButton(ItemInterface $menu, OrderInterface $order)
    {
        $possibleTransitions = $this->stateMachineFactory->get($order, OdooOrderInterface::GRAPH)->getPossibleTransitions();

        if (empty($possibleTransitions)) {
            return;
        }

        $returns = $menu
            ->addChild('odoo')
            ->setLabel('Renvoyer à Odoo')
            ->setAttribute('type', 'select')
            ->setLabelAttribute('icon', 'list')
            ->setLabelAttribute('color', 'olive');

        foreach ($possibleTransitions as $possibleTransition) {
            switch ($possibleTransition) {
                case OdooOrderInterface::ODOO_TRANSITION_CANCEL:
                    $returns->addChild('cancel', [
                        'route' => Cancel::ROUTE,
                        'routeParameters' => [
                            'id' => $order->getId(),
                        ],
                    ])->setLabel('Annulation');
                    break;
                case OdooOrderInterface::ODOO_TRANSITION_DESTOCK:
                    $returns->addChild('destock', [
                        'route' => Destock::ROUTE,
                        'routeParameters' => [
                            'id' => $order->getId(),
                        ],
                    ])->setLabel('Mouvement stock');
                    break;
                case OdooOrderInterface::ODOO_TRANSITION_SHIP:
                    $returns->addChild('ship', [
                        'route' => Ship::ROUTE,
                        'routeParameters' => [
                            'id' => $order->getId(),
                        ],
                    ])->setLabel('Expédition');
                    break;
                case OdooOrderInterface::ODOO_TRANSITION_CONFIRM_RETURN:
                    $returns->addChild('confirm_return', [
                        'route' => ConfirmReturn::ROUTE,
                        'routeParameters' => [
                            'id' => $order->getId(),
                        ],
                    ])->setLabel("Confirmer l'expédition du retour");
                    break;
                case OdooOrderInterface::ODOO_TRANSITION_CREATE:
                case OdooOrderInterface::ODOO_TRANSITION_CREATE_RETURN:
                    $returns->addChild('create', [
                        'route' => Create::ROUTE,
                        'routeParameters' => [
                            'id' => $order->getId(),
                        ],
                    ])->setLabel("Créer au sein d'Odoo");
                    break;
                default:
                    break;
            }
        }
    }
}
