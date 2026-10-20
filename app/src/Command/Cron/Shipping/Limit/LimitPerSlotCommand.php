<?php

namespace App\Command\Cron\Shipping\Limit;

use App\Command\Cron\Shipping\AbstractLimit;
use App\Doctrine\Entity\ExceptionalSchedule;
use App\Doctrine\Entity\ServiceRules;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Enum\EmailConfigurationName;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class LimitPerSlotCommand extends AbstractLimit
{
    protected static $defaultName = 'app:cron:shipping:limit-per-slot';

    protected function configure(): void
    {
        $this->setDescription('Cron Shipping Limit Per Slot');
    }

    /**
     * @throws \Exception
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limitsBySlotReached = $this->shipmentRepository->findLimitsPerSlotReached();
        $bodyMail = '';

        foreach ($limitsBySlotReached as $limit) {
            /** @var ShippingMethod $method */
            $method = $this->shippingMethodRepository->find($limit['id']);
            $dateStart = new \DateTime($limit['date_limit_start']);
            $dateEnd = new \DateTime($limit['date_limit_end']);

            $shippingMethodGroup = $method->getShippingMethodGroup();
            $shippingMethodGroups = $shippingMethodGroup->getShippingMethods();
            foreach ($shippingMethodGroups as $shippingMethod) {
                $exceptionalSchedule = new ExceptionalSchedule();
                $exceptionalSchedule->setType(ExceptionalSchedule::EXCEPTIONAL_TYPE_CLOSING_SLOT);
                $exceptionalSchedule->setStart($dateStart);
                $exceptionalSchedule->setEnd($dateEnd);
                $exceptionalSchedule->setShippingMethod($shippingMethod);

                $serviceRules = $this->serviceRulesRepository->findBy(['shippingMethod' => $shippingMethod]);

                /** @var ServiceRules $serviceRule */
                foreach ($serviceRules as $serviceRule) {
                    // Check if service rules is not already closed
                    foreach ($serviceRule->getExceptionalSchedule() as $exceptionalServiceRuleSchedule) {
                        if (!$this->dateHelper->exceptionalScheduleHelper->isOpen($dateStart, $exceptionalServiceRuleSchedule)) {
                            continue 2;
                        }
                    }
                    $exceptionalServiceRuleSchedule = clone $exceptionalSchedule;
                    $exceptionalServiceRuleSchedule->setShippingMethod(null);
                    $exceptionalServiceRuleSchedule->setServiceRules($serviceRule);

                    $this->exceptionScheduleRepository->add($exceptionalServiceRuleSchedule);
                    $bodyMail .= $serviceRule->getName() . ' => ' . $dateStart->format('d/m/Y H:i:s') . ' à ' . $dateEnd->format('H:i:s') . '<br />';
                }
                foreach ($shippingMethod->getExceptionalSchedule() as $existingExceptionalSchedule) {
                    if (!$this->dateHelper->exceptionalScheduleHelper->isOpen($dateStart, $existingExceptionalSchedule)) {
                        continue 2;
                    }
                }

                $this->exceptionScheduleRepository->add($exceptionalSchedule);
                $bodyMail .= $shippingMethod->getName() . ' => ' . $dateStart->format('d/m/Y H:i:s') . ' à ' . $dateEnd->format('H:i:s') . '<br />';
            }
        }

        if ('' !== $bodyMail) {
            $body = 'Hello,<br/><br/>
                Des fermetures de créneaux ont été générés car la limite de livraison par créneau a été atteinte pour le(s) transporteur(s) à la/aux date(s) suivante(s) :<br /><br />' .
                $bodyMail .
                "<br />Cordialement,<br /><br />
                L'équipe IT<br /><br />
                Tediber";

            $message = (new Email())
                ->subject('Sylius transporteurs : Fermeture de créneaux automatique')
                ->from(new Address('dev@tediber.com', 'TEDIBER'))
                ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::AUTOMATIC_SLOT_CLOSING)->getPrincipals())
                ->html($body);

            $this->mailer->send($message);
        }

        return 0;
    }
}
