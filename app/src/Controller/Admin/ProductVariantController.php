<?php

namespace App\Controller\Admin;

use App\Services\Mail\Mailer\VariantDisabledEmail;
use Sylius\Bundle\CoreBundle\Controller\ProductVariantController as ProductVariantBase;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductVariantController extends ProductVariantBase
{
    public function enabledAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $entityManager = $this->getDoctrine()->getManager();

        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        $resource = $this->findOr404($configuration);

        if (!$resource->isEnabled()) {
            $resource->enable();
        } else {
            $resource->disable();
            $this->get(VariantDisabledEmail::class)->generateEmail($resource->getCode(), $this->getUser());
        }

        $entityManager->persist($resource);
        $entityManager->flush();

        return $this->redirect($request->headers->get('referer'));
    }
}
