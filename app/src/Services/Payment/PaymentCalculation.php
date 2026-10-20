<?php

namespace App\Services\Payment;

class PaymentCalculation
{
    public function calculateAmounts(float $amount, $date = null): array
    {
        $amounts = [];
        $floatBaseMonthAmount = $amount / 3;
        $intBaseAmountMonthly = intval($floatBaseMonthAmount);
        $date ??= new \DateTime();

        $date1 = $date;
        $date2 = new \DateTime($date->format('Y-m-d') . ' +1 month');
        $date3 = new \DateTime($date->format('Y-m-d') . ' +2 months');

        $amounts['AMOUNTS'][$date1->format('Y-m-d')] = ($intBaseAmountMonthly + ($amount - (3 * $intBaseAmountMonthly))) * 100;
        $amounts['AMOUNTS'][$date2->format('Y-m-d')] = $intBaseAmountMonthly * 100;
        $amounts['AMOUNTS'][$date3->format('Y-m-d')] = $intBaseAmountMonthly * 100;

        return $amounts;
    }
}
