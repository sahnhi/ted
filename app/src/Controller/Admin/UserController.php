<?php

namespace App\Controller\Admin;

use Sylius\Bundle\UserBundle\Controller\UserController as BaseUserController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserController extends BaseUserController
{
    public function updateAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $entity = $this->findOr404($configuration);

        if (!$this->getUser()->hasRole('ROLE_ADMINISTRATION_ACCESS')
            && $entity->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        return parent::updateAction($request);
    }
}
