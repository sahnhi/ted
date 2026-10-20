<?php
/**
 * Created by PhpStorm.
 * User: florent
 * Date: 19/03/19
 * Time: 16:43.
 */

namespace App\View;

use App\Doctrine\Entity\ExceptionalSchedule;
use App\Doctrine\Entity\ServiceRules;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Services\Date\DateHelper;

class ShipmentTransmissionView
{
    public ShippingMethodTransmissionView $method;
    public ?\DateTime $startDeliverySlot = null;
    public ?\DateTime $endDeliverySlot = null;
    public ?\DateTimeInterface $checkoutCompletedAt = null;
    public DateHelper $dateHelper;
    public ShippingMethodInterface $shippingMethod;
    public ?ServiceRules $serviceRules = null;
    public ?string $relayId = null;

    /**
     * ShipmentTransmissionView constructor.
     */
    public function __construct()
    {
        $this->method = new ShippingMethodTransmissionView();
    }

    public function getDebutCreneau(): string
    {
        return $this->startDeliverySlot->format('d/m/Y H:i:s');
    }

    public function getFinCreneau(): string
    {
        return $this->endDeliverySlot->format('d/m/Y H:i:s');
    }

    public function getCommentary(): string
    {
        return 'LIV IMP LE ' . $this->startDeliverySlot->format('d/m \d\e H\Hi') . ' à ' . $this->endDeliverySlot->format('H\Hi') . ' EXP LE ' . $this->getShippingDate(true);
    }

    public function getRelayId(): ?string
    {
        return $this->relayId;
    }

    public function getShippingDate(bool $firstCall = false): string
    {
        if (str_contains($this->method->code, ShippingMethodInterface::METHOD_EDELIV)) {
            $shippingDate = $this->startDeliverySlot;
        } else {
            $transportTime = intval(24 * $this->method->transportTime);
            $shippingDate = new \DateTime($this->startDeliverySlot->format('y-m-d h:i:s'));
            $shippingDate = $this->smartSub($shippingDate, new \DateInterval('PT' . $transportTime . 'H'));
            if (null !== $this->method->shippingTimeDate && true === $firstCall) {
                $shippingTimeDate = intval(24 * $this->method->shippingTimeDate);
                $this->smartSub($shippingDate, new \DateInterval('PT' . $shippingTimeDate . 'H'));
            }

            $shippingDate = $this->checkWarehouseAndShippingMethods($shippingDate);

            if (str_contains($this->method->code, ShippingMethodInterface::METHOD_VIR)) {
                if ($shippingDate < new \DateTime()) {
                    $shippingDate = new \DateTime();
                }
            }
        }

        return $shippingDate->format('ymd');
    }

    private function getFirstDateFromExceptionalShippingDates(array $schedules, \DateTime $shippingDate): \DateTime
    {
        foreach ($schedules as $schedule) {
            if (!$this->dateHelper->exceptionalScheduleHelper->isOpen($shippingDate, $schedule)) {
                $interval = new \DateInterval('P1D');
                $yesterday = clone $shippingDate;
                $yesterday->sub($interval);
                break;
            }
        }

        if (isset($yesterday)) {
            $shippingDate = $this->getFirstDateFromExceptionalShippingDates($schedules, $yesterday);
        }

        return $shippingDate;
    }

    private function checkWarehouseAndShippingMethods(\DateTime $shippingDate): \DateTime
    {
        $oldDate = clone $shippingDate;

        $schedules = $this->shippingMethod->getExceptionalSchedule();
        $schedules = null !== $this->serviceRules ? array_merge($schedules->toArray(), $this->serviceRules->getExceptionalSchedule()->toArray()) : $schedules->toArray();

        /** @var ExceptionalSchedule $schedule */
        foreach ($schedules as $key => $schedule) {
            if (in_array($schedule->getType(), [ExceptionalSchedule::EXCEPTIONAL_TYPE_CLOSING_SLOT, ExceptionalSchedule::EXCEPTIONAL_TYPE_OPENING_SLOT])) {
                unset($schedules[$key]);
            }
        }

        $shippingDate = $this->getFirstDateFromExceptionalShippingDates($schedules, $shippingDate);

        // TODO : Add case where ServiceRules has different warehouse than ShippingMethod
        // Check if Warehouse is open, if not, get first previous available date
        $warehouse = $this->dateHelper->warehouseDateHelper->getWarehouse($this->method->code);
        if (null !== $warehouse) {
            $shippingDate = $this->dateHelper->warehouseDateHelper->isDateAvailable($warehouse, $shippingDate) ? $shippingDate : $this->dateHelper->warehouseDateHelper->getFirstPreviousDateAvailable($warehouse, $shippingDate);
        }

        // Finally, check if ShippingMethod is still open at this new date
        $shippingDate = $this->dateHelper->shippingMethodDateHelper->getFirstDateAvailable($shippingDate, $this->shippingMethod);

        return $shippingDate->format('U') !== $oldDate->format('U') ? $this->checkWarehouseAndShippingMethods($shippingDate) : $shippingDate;
    }

    private function smartSub(\DateTime $shippingDate, \DateInterval $interval): \DateTime
    {
        $intervalHours = $interval->h;
        $nbDays = floor($intervalHours / 24);
        $nbHours = $intervalHours - ($nbDays * 24);

        while ($nbDays > 0) {
            $shippingDate->sub(new \DateInterval('P1D'));

            if ($this->dateHelper->shippingMethodDateHelper->isOpen($shippingDate, $this->shippingMethod)) {
                --$nbDays;
            }
        }

        $finalDate = clone $shippingDate;
        $finalDate->sub(new \DateInterval('PT' . $nbHours . 'H'));

        if ($finalDate->format('Y-m-d') !== $shippingDate->format('Y-m-d')) {
            if (!$this->dateHelper->shippingMethodDateHelper->isOpen($finalDate, $this->shippingMethod)) {
                $finalDate = $this->dateHelper->shippingMethodDateHelper->getFirstDateAvailable($finalDate, $this->shippingMethod);
            }
        }

        return $finalDate;
    }
}
