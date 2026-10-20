<?php

namespace App\Services\Calendar\Maker;

interface TimeMakerInterface
{
    public function makeTime($timeMaker): array;
}
