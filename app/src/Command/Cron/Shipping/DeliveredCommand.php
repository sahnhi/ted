<?php

namespace App\Command\Cron\Shipping;

use App\Services\Transmission\Entrepot\Spidy\Deliver;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use SM\SMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeliveredCommand extends Command
{
    protected static $defaultName = 'app:cron:shipping:delivered';

    public function __construct(
        private readonly Deliver $deliver
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Mail Review');
    }

    /**
     * @throws SMException
     * @throws ORMException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->deliver->updateDelivered();

        return 0;
    }
}
