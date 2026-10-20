<?php

namespace App\Command\Cron\Shipping;

use App\Doctrine\Repository\ExceptionalScheduleRepository;
use App\Doctrine\Repository\Sylius\ShipmentRepository;
use App\Registry\EmailConfigurationRegistryInterface;
use App\Services\Date\DateHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Mailer\MailerInterface;

abstract class AbstractLimit extends Command
{
    public function __construct(
        protected ShipmentRepository $shipmentRepository,
        protected EntityRepository $shippingMethodRepository,
        protected ExceptionalScheduleRepository $exceptionScheduleRepository,
        protected EntityRepository $serviceRulesRepository,
        protected DateHelper $dateHelper,
        protected MailerInterface $mailer,
        protected EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
        parent::__construct();
    }
}
