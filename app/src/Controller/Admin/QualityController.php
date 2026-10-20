<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Services\Quality\Export\InvalidExportFormatException;
use App\Services\Quality\Export\QualityCsvGeneratorInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class QualityController extends ResourceController
{
    /**
     * @throws InvalidExportFormatException
     */
    public function exportCsvAction(Request $request, QualityCsvGeneratorInterface $qualityCsvGenerator): StreamedResponse
    {
        if (QualityCsvGeneratorInterface::FORMAT !== $request->query->get('export')) {
            throw new InvalidExportFormatException($request->query->get('export'));
        }

        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);

        return $qualityCsvGenerator->generateCsvFromResourceGridView($resources, $request->getSession());
    }
}
