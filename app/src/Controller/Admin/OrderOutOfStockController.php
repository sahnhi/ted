<?php

namespace App\Controller\Admin;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderOutOfStockController extends ResourceController
{
    public const NONE_VALUE = 'none';

    public function bulkEditAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::INDEX);

        $resources = $this->repository->findBy(['order' => $request->get('ids', [])]);

        foreach ($resources as $resource) {
            $field = $request->get('field');
            $getter = 'get' . ucfirst((string) $field);
            $setter = 'set' . ucfirst((string) $field);
            $value = $request->get($field, null);
            $type = $request->get('type', null);

            if (in_array($value, [self::NONE_VALUE, '', 'null'])) {
                $value = null;
            } else {
                if (null !== $type) {
                    switch ($type) {
                        case 'date':
                            $value = new \DateTime($value);
                            break;
                    }
                }
            }

            if ($resource->$getter() !== $value) {
                $resource->$setter($value);
                $this->manager->persist($resource);
            }

            $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);
        }

        $this->manager->flush();

        return $this->redirectHandler->redirectToReferer($configuration);
    }
}
