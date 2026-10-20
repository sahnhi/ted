<?php

namespace App\Services\Calendar\Maker;

use Symfony\Component\Validator\Constraints\Time;

class TimeMaker implements TimeMakerInterface
{
    /**
     * @var int
     */
    private $place;

    /**
     * @var string
     */
    private $providerName;

    /**
     * @var Time
     */
    private $startTime;

    /**
     * @var Time
     */
    private $endTime;

    public function makeTime($makeTime): array
    {
        $this->convertTimeToString($makeTime);

        /** @var array $time */
        $time = [
            'place' => $makeTime['place'],
            'providerName' => $makeTime['provider_name'],
            'dailyDelivery' => $makeTime['dailyDelivery'],
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'shipping_message' => $makeTime['shipping_message'],
        ];

        return $time;
    }

    public function convertTimeToString($dateTime)
    {
        /** @var \DateTime $startTime */
        $startTime = $dateTime['start_time'];
        /** @var \DateTime $endTime */
        $endTime = $dateTime['end_time'];

        $this->startTime = $startTime->format('H:i');
        $this->endTime = $endTime->format('H:i');
    }
}
