<?php

namespace App\Controller\Admin;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FamiliarityController extends ResourceController
{
    public function enabledAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $entityManager = $this->getDoctrine()->getManager();

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        $resource = $this->findOr404($configuration);

        if (!$resource->getEnabled()) {
            $resource->setEnabled(true);
        } else {
            $resource->setEnabled(false);
        }

        $entityManager->persist($resource);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
