<?php

namespace App\Command;

use App\Doctrine\Repository\Sylius\CustomerRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Model\CrossSell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCohorteCommand extends Command
{
    public const MIN_YEAR = 2015;

    protected static $defaultName = 'app:export-cohorte';

    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly OrderRepository $orderRepository,
        private readonly CrossSell $crossSell
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('ExportCohorte')
            ->addArgument('csv', InputArgument::REQUIRED, 'Path to csv file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csv = $input->getArgument('csv');
        $offset = 0;
        $limit = 100;
        $allTrimestre = $this->prepareTrimestre();

        $handle = fopen($csv, 'w+');

        $keys = ['Email', 'N° 1ere commande', 'Trimestre première commande', 'Type première commande'];

        $i = 0;
        foreach ($allTrimestre as $key => $value) {
            $keys[] = 'Commande au moins un matelas ' . $key;
            $keys[] = 'Commande sans matelas ' . $key;
            ++$i;
        }

        fputcsv($handle, $keys, ';');

        do {
            $results = $this->customerRepository->findBy([], [], $limit, $offset);

            foreach ($results as $customer) {
                if ($customer->getOrders()) {
                    $firstOrderArray = $this->orderRepository->customerFirstOrder($customer->getId());
                    if (!empty($firstOrderArray)) {
                        $firstCommandNumber = $firstOrderArray[0]['number'];
                        $checkoutCompletedAt = $firstOrderArray[0]['checkoutCompletedAt'];
                        $id = $firstOrderArray[0]['id'];
                        $year = $checkoutCompletedAt->format('Y');
                        $trimestre = $this->getTrimestre($checkoutCompletedAt->format('m'));

                        $order = $this->orderRepository->find($id);
                        $crossSell = $this->crossSell->getCrossSell($order->getItems());

                        $orders = $this->orderRepository->findBy(['customer' => $customer->getId(), 'paymentState' => 'paid']);
                        $ordersTrimestreMattress = $this->prepareTrimestre();
                        $ordersTrimestreWithoutMattress = $this->prepareTrimestre();

                        foreach ($orders as $order) {
                            $checkoutCompletedAt = $order->getCheckoutCompletedAt();
                            $year = $checkoutCompletedAt->format('Y');
                            $trimestre = $this->getTrimestre($checkoutCompletedAt->format('m'));

                            $crossSell = $this->crossSell->getCrossSell($order->getItems());

                            if (CrossSell::MATTRESS_ONLY === $crossSell || CrossSell::MATTRESS_AND_BED_BASE === $crossSell || CrossSell::MATTRESS_WITHOUT_BED_BASE === $crossSell) {
                                $ordersTrimestreMattress[$year . 'T' . $trimestre] = $ordersTrimestreMattress[$year . 'T' . $trimestre] + 1;
                            } else {
                                $ordersTrimestreWithoutMattress[$year . 'T' . $trimestre] = $ordersTrimestreWithoutMattress[$year . 'T' . $trimestre] + 1;
                            }
                        }

                        $keys = [$customer->getEmail(), $firstCommandNumber, $year . 'T' . $trimestre, $crossSell];

                        $allTrimestre = $this->prepareTrimestre();

                        foreach ($allTrimestre as $key => $value) {
                            $keys[] = $ordersTrimestreMattress[$key];
                            $keys[] = $ordersTrimestreWithoutMattress[$key];
                        }

                        fputcsv($handle, $keys, ';');
                    }
                }
            }

            $offset += $limit;
        } while ($results);

        fclose($handle);

        // todo replace by COMMAND::SUCCESS on version 5.1
        return 0;
    }

    /**
     * @return int[]
     */
    private function prepareTrimestre(): array
    {
        $ordersTrimestre = [];
        $maxYear = date('Y');
        $maxTrimestre = $this->getTrimestre(date('m'));
        $year = self::MIN_YEAR;
        $trimestre = 1;

        while ($year <= $maxYear && $trimestre <= $maxTrimestre) {
            $ordersTrimestre[$year . 'T' . $trimestre] = 0;

            if (4 === $trimestre) {
                $trimestre = 1;
                ++$year;
            } else {
                ++$trimestre;
            }
        }

        return $ordersTrimestre;
    }

    private function getTrimestre($mounth)
    {
        if ($mounth < 4) {
            return 1;
        } elseif ($mounth < 7) {
            return 2;
        } elseif ($mounth < 10) {
            return 3;
        } else {
            return 4;
        }
    }
}
