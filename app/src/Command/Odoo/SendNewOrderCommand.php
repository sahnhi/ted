<?php

declare(strict_types=1);

namespace App\Command\Odoo;

use App\Api\Odoo\Action\CreateOrder;
use App\Api\Odoo\Action\DestockOrder;
use App\Api\Odoo\Action\ShipOrder;
use App\Doctrine\Entity\Sylius\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendNewOrderCommand extends Command
{
    protected static $defaultName = 'app:odoo:send-new-order';

    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly CreateOrder $createOrder,
        private readonly ShipOrder $shipOrder,
        private readonly DestockOrder $destockOrder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('From the order id parameter this command will find the order and the to the odoo order craetion endpoint')
            ->addOption('order-id', 'i', InputOption::VALUE_OPTIONAL, 'Order ID')
            ->addOption('order-number', 'o', InputOption::VALUE_OPTIONAL, 'Order number')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orderDebugInfo = '';
        if ($input->getOption('order-id')) {
            $orderId = $input->getOption('order-id');
            $orderDebugInfo = $orderId;
            $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        } elseif ($input->getOption('order-number')) {
            $orderNumber = $input->getOption('order-number');
            $orderNumber = str_pad(trim((string) $orderNumber), 9, '0', STR_PAD_LEFT);
            $orderDebugInfo = $orderNumber;
            $order = $this->orderRepository->findOneBy(['number' => $orderNumber]);
        }

        if (!isset($order) || !$order instanceof OrderInterface) {
            $orderInfo = $input->getOption('order-id') ? 'with ID' : 'with number';
            $output->writeln(sprintf('Order %s %s not found !', $orderInfo, $orderDebugInfo));

            return 1;
        }

        $output->writeln('Order ' . $order->getId() . ' in progress !');
        try {
            $this->createOrder->__invoke($order);
            if (OrderInterface::STATE_CANCELLED !== $order->getState()) {
                $this->destockOrder->__invoke($order);
                $this->shipOrder->__invoke($order);
            }
            $output->writeln('Order ' . $order->getId() . ' sent to Odoo !');
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }
}
