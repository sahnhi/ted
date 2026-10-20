<?php

namespace App\Command;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixMissingCustomerInfosCommand extends Command
{
    protected static $defaultName = 'app:fix:missing-customer-infos';

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('Starting to fix missing firstnames for customers.');
        $this->fixCustomerFirstname($output);
        $output->writeln("\nFinished fixing missing firstnames for customers.");

        $output->writeln('Starting to fix missing lastnames for customers.');
        $this->fixCustomerLastname($output);
        $output->writeln("\nFinished fixing missing lastnames for customers.");

        $io->success('All customer that could be fixed have been fixed.');

        return 0;
    }

    private function fixCustomerFirstname(OutputInterface $output): void
    {
        /**
         * @var Customer[] $customers
         */
        $customers = $this->entityManager->getRepository(Customer::class)
            ->createQueryBuilder('c')
            ->innerJoin('c.orders', 'o')
            ->andWhere("(c.firstName IS NULL or c.firstName = '')")
            ->andWhere('o.checkoutState = :state')
            ->setParameter('state', OrderCheckoutStates::STATE_COMPLETED)
            ->getQuery()
            ->getResult();

        $progressBar = new ProgressBar($output, count($customers));

        foreach ($customers as $index => $customer) {
            $progressBar->advance();
            $lastAddress = $customer->getAddresses()->filter(
                fn ($address): bool => (bool) $address->getFirstname()
            )->last();

            if (false === $lastAddress) {
                $lastAddress = $customer->getOrders()->filter(
                    function (Order $order): bool {
                        if ((bool) $order->getBillingAddress()?->getFirstName()) {
                            return OrderCheckoutStates::STATE_COMPLETED === $order->getCheckoutState();
                        }

                        return false;
                    }
                )->last();

                if (false === $lastAddress) {
                    $lastAddress = $customer->getOrders()->filter(
                        function (Order $order): bool {
                            if ((bool) $order->getBillingAddress()?->getFirstName()) {
                                return OrderCheckoutStates::STATE_COMPLETED === $order->getCheckoutState();
                            }

                            return false;
                        }
                    )->last();

                    if (false === $lastAddress) {
                        continue;
                    } else {
                        $lastAddress = $lastAddress->getBillingAddress();
                    }
                } else {
                    $lastAddress = $lastAddress->getBillingAddress();
                }
            }

            $customer->setFirstName($lastAddress->getFirstName());
            $this->entityManager->persist($customer);

            if (0 === $index % 100) {
                $this->entityManager->flush();
            }
        }
        $this->entityManager->flush();
        $progressBar->finish();
    }

    private function fixCustomerLastname(OutputInterface $output): void
    {
        /**
         * @var Customer[] $customers
         */
        $customers = $this->entityManager->getRepository(Customer::class)
            ->createQueryBuilder('c')
            ->innerJoin('c.orders', 'o')
            ->andWhere("(c.lastName IS NULL or c.lastName = '')")
            ->andWhere('o.checkoutState = :state')
            ->setParameter('state', OrderCheckoutStates::STATE_COMPLETED)
            ->getQuery()
            ->getResult();

        $progressBar = new ProgressBar($output, count($customers));
        foreach ($customers as $index => $customer) {
            $progressBar->advance();
            $lastAddress = $customer->getAddresses()->filter(
                fn ($address): bool => (bool) $address->getLastname()
            )->last();

            if (false === $lastAddress) {
                $lastAddress = $customer->getOrders()->filter(
                    function (Order $order): bool {
                        if ((bool) $order->getBillingAddress()?->getLastName()) {
                            return OrderCheckoutStates::STATE_COMPLETED === $order->getCheckoutState();
                        }

                        return false;
                    }
                )->last();

                if (false === $lastAddress) {
                    $lastAddress = $customer->getOrders()->filter(
                        function (Order $order): bool {
                            if ((bool) $order->getBillingAddress()?->getLastName()) {
                                return OrderCheckoutStates::STATE_COMPLETED === $order->getCheckoutState();
                            }

                            return false;
                        }
                    )->last();

                    if (false === $lastAddress) {
                        continue;
                    } else {
                        $lastAddress = $lastAddress->getBillingAddress();
                    }
                } else {
                    $lastAddress = $lastAddress->getBillingAddress();
                }
            }

            $customer->setLastName($lastAddress->getLastName());
            $this->entityManager->persist($customer);

            if (0 === $index % 100) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();
        $progressBar->finish();
    }
}
