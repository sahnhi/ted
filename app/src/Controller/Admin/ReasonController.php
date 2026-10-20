<?php

namespace App\Controller\Admin;

use App\Doctrine\Entity\Reason;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReasonController extends ResourceController
{
    public function placeholderAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        /** @var Reason $resource */
        $resource = $this->findOr404($configuration);

        return new JsonResponse([
            'placeholder' => $resource->getPlaceholder(),
            'productRule' => $resource->getProductRule(),
        ]);
    }
}
