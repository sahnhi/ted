<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Controller;

use App\Api\ChronoPost\Finder\ChronoRelaisFinderInterface;
use App\Api\ChronoPost\Query\ChronoRelaisQueryFactory;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\Sylius\ShippingMethodRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetChronoRelais extends AbstractController
{
    public function __construct(
        private readonly ChronoRelaisFinderInterface $chronoRelaisFinder,
        private readonly ChronoRelaisQueryFactory $chronoRelaisQueryFactory,
        private readonly ShippingMethodRepository $shippingMethodRepository
    ) {
    }

    /**
     * @throws \Exception
     */
    public function getForFront(Request $request): JsonResponse
    {
        $chronoRelaisQuery = $this->chronoRelaisQueryFactory->fromQueryParams($request->query);
        $chronoRelaisList = $this->chronoRelaisFinder->findNearBy($chronoRelaisQuery);

        return new JsonResponse($chronoRelaisList, Response::HTTP_OK);
    }

    /**
     * @throws \Exception
     */
    public function getForAdmin(Request $request): JsonResponse
    {
        $query = $this->chronoRelaisQueryFactory->fromAdmin($request->query);
        $relayList = $this->chronoRelaisFinder->findNearBy($query);
        /** @var ShippingMethod $shippingMethod */
        $shippingMethod = $this->shippingMethodRepository->findOneBy(['code' => ShippingMethodInterface::METHOD_CHRONOPOST]);

        return new JsonResponse(
            $this->renderView('@App/Shipment/Partial/_relays.html.twig', [
                'relays' => $relayList->toArray(),
                'shippingMethodCode' => $shippingMethod->getCode(),
                'shippingMethodIdBalou' => $shippingMethod->getIdShippingMethodBalou(),
            ]),
            Response::HTTP_OK
        );
    }
}
