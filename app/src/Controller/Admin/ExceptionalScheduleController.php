<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Doctrine\Entity\ShippingMethodGroup;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionalScheduleController extends ResourceController
{
    public function createAction(Request $request): Response
    {
        /** @var array<string, mixed> $requestData */
        $requestData = (array) $request->request->get('exceptional_schedule');

        if (!empty($requestData['shippingMethodGroup']) && $request->isMethod(Request::METHOD_POST)) {
            /** @var RepositoryInterface $shippingMethodGroupRepository */
            $shippingMethodGroupRepository = $this->get('app.repository.shipping_method_group');

            /** @var ShippingMethodGroup $shippingMethodGroup */
            $shippingMethodGroup = $shippingMethodGroupRepository->find($requestData['shippingMethodGroup']);

            foreach ($shippingMethodGroup->getShippingMethods() as $shippingMethod) {
                $requestData['shippingMethod'] = $shippingMethod->getId();

                $request->request->set('exceptional_schedule', $requestData);

                parent::createAction($request);
            }

            return $this->redirectToRoute('sylius_admin_exceptional_schedule_index');
        }

        return parent::createAction($request);
    }
}
