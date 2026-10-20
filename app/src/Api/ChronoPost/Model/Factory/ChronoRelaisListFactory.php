<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Model\Factory;

use App\Api\ChronoPost\Model\ChronoRelais;
use App\Api\ChronoPost\Model\ChronoRelaisList;

final class ChronoRelaisListFactory
{
    public function createFromSoapResponse(array $chronoRelaisListAsArray): ChronoRelaisList
    {
        if (empty($chronoRelaisListAsArray)) {
            return new ChronoRelaisList($chronoRelaisListAsArray);
        }

        if (isset($chronoRelaisListAsArray['accesPersonneMobiliteReduite'])) {
            return new ChronoRelaisList([ChronoRelais::fromChronoRelaisResponse($chronoRelaisListAsArray)]);
        }

        return new ChronoRelaisList(
            array_map(
                static fn (array $chronoRelais): ChronoRelais => ChronoRelais::fromChronoRelaisResponse($chronoRelais),
                $chronoRelaisListAsArray
            )
        );
    }
}
