<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Finder;

use App\Api\ChronoPost\Client\ChronoRelaisClientInterface;
use App\Api\ChronoPost\Model\ChronoRelaisList;
use App\Api\ChronoPost\Model\Factory\ChronoRelaisListFactory;
use App\Api\ChronoPost\Query\ChronoRelaisQuery;

final class ChronoRelaisFinder implements ChronoRelaisFinderInterface
{
    public function __construct(
        private readonly ChronoRelaisClientInterface $chronoRelaisClient,
        private readonly ChronoRelaisListFactory $chronoRelaisListFactory
    ) {
    }

    public function findNearBy(ChronoRelaisQuery $chronoRelaisQuery): ChronoRelaisList
    {
        $soapResponseAsArray = $this->chronoRelaisClient->findNearBy($chronoRelaisQuery);

        return $this->chronoRelaisListFactory->createFromSoapResponse($soapResponseAsArray);
    }
}
