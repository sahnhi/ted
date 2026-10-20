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

final class LimitCommand extends AbstractLimit
{
    protected static $defaultName = 'app:cron:shipping:limit';

    protected function configure(): void
    {
        $this->setDescription('Cron Shipping Limit');
    }

    /**
     * @throws \Exception
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limitsReached = $this->shipmentRepository->findLimitsReached();
        $bodyMail = '';

        foreach ($limitsReached as $limit) {
            /** @var ShippingMethod $method */
            $method = $this->shippingMethodRepository->find($limit['id']);
            $shippingMethodGroup = $method->getShippingMethodGroup();
            $shippingMethodGroups = $shippingMethodGroup->getShippingMethods();
            $date = new \DateTime($limit['date_limit']);
            $end = clone $date;
            $date->setTime($method->getOpeningTime()->format('H'), $method->getOpeningTime()->format('i'));
            $end->setTime($method->getClosingTime()->format('H'), $method->getClosingTime()->format('i'));

            /** @var ShippingMethod $shippingMethod */
            foreach ($shippingMethodGroups as $shippingMethod) {
                $exceptionalSchedule = new ExceptionalSchedule();
                $exceptionalSchedule->setType(ExceptionalSchedule::EXCEPTIONAL_TYPE_CLOSING_SLOT);
                $exceptionalSchedule->setStart($date);
                $exceptionalSchedule->setEnd($end);
                $exceptionalSchedule->setShippingMethod($shippingMethod);

                $serviceRules = $this->serviceRulesRepository->findBy(['shippingMethod' => $shippingMethod]);
                /** @var ServiceRules $serviceRule */
                foreach ($serviceRules as $serviceRule) {
                    // Check if service rules is not already closed
                    foreach ($serviceRule->getExceptionalSchedule() as $exceptionalServiceRuleSchedule) {
                        if (!$this->dateHelper->exceptionalScheduleHelper->isOpen($date, $exceptionalServiceRuleSchedule)) {
                            continue 2;
                        }
                    }

                    $exceptionalServiceRuleSchedule = clone $exceptionalSchedule;
                    $exceptionalServiceRuleSchedule->setShippingMethod(null);
                    $exceptionalServiceRuleSchedule->setServiceRules($serviceRule);

                    $this->exceptionScheduleRepository->add($exceptionalServiceRuleSchedule);
                    $bodyMail .= $serviceRule->getName() . ' => ' . $date->format('d/m/Y') . '<br />';
                }

                foreach ($method->getExceptionalSchedule() as $existingExceptionalSchedule) {
                    if (!$this->dateHelper->exceptionalScheduleHelper->isOpen($date, $existingExceptionalSchedule)) {
                        continue 2;
                    }
                }

                $this->exceptionScheduleRepository->add($exceptionalSchedule);
                $bodyMail .= $method->getName() . ' => ' . $date->format('d/m/Y') . '<br />';
            }
        }

        if ('' !== $bodyMail) {
            $body = 'Hello,<br/><br/>
                Des fermetures de créneaux ont été générés car la limite quotidienne de livraison a été atteinte pour le(s) transporteur(s) à la/aux date(s) suivante(s) :<br /><br />' .
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

        // Now clean up old ExceptionalSchedules
        $today = new \DateTime();
        $deadline = $today->sub(new \DateInterval('P1M'));
        $cleanArray = $this->exceptionScheduleRepository->getNonCalendarUntilDate($deadline);

        foreach ($cleanArray as $exceptionalSchedule) {
            $this->exceptionScheduleRepository->remove($exceptionalSchedule);
        }

        return 0;
    }
}
