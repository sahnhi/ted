<?php

namespace App\Controller\Admin;

use App\Services\Admin\Export\ExportCsv;
use Sylius\Bundle\PromotionBundle\Controller\PromotionCouponController as BasePromotionCouponController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromotionCouponController extends BasePromotionCouponController
{
    public function indexAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::INDEX);

        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);

        $this->eventDispatcher->dispatchMultiple(ResourceActions::INDEX, $configuration, $resources);

        if ('csv' === $request->get('export')) {
            $data = [];
            $data[] = ['Code', 'Usage Limit', 'Per Customer Usage Limit', 'Used', 'Expires At', 'Per Customer Usage Limit'];

            $exportCsv = new ExportCsv();
            for ($i = 0; $i < $resources->getData()->getNbPages(); ++$i) {
                $query = $exportCsv->parseResource($resources, $i)->iterate();

                while (false !== ($row = $query->next())) {
                    $data[] = [$row[0]->getCode(), $row[0]->getUsageLimit(), $row[0]->getPerCustomerUsageLimit(), $row[0]->getUsed(), null !== $row[0]->getExpiresAt() ? $row[0]->getExpiresAt()->format('d/m/Y') : '', $row[0]->getPerCustomerUsageLimit()];

                    $this->getDoctrine()->getManager()->detach($row[0]);
                }
            }

            return $exportCsv->generateCsv($data, 'promotion-coupon-' . $request->get('promotionId'));
        }

        if ($configuration->isHtmlRequest()) {
            return $this->render($configuration->getTemplate(ResourceActions::INDEX . '.html'), [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resources' => $resources,
                $this->metadata->getPluralName() => $resources,
            ]);
        }

        return $this->createRestView($configuration, $resources);
    }
}
