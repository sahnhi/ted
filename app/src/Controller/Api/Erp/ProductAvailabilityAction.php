<?php

namespace App\Controller\Api\Erp;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Factory\Calendar\Request\CalendarAvailabilityRequestFactory;
use App\Services\Calendar\VariantService;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ProductAvailabilityAction
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly VariantService $variantService,
        private readonly CalendarAvailabilityRequestFactory $calendarAvailabilityRequestFactory,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): Response
    {
        $calendarAvailabilityRequest = $this->calendarAvailabilityRequestFactory->createFromRequest($request);

        $code = $request->attributes->get('variantCode');
        /** @var ProductVariant|null $variant */
        $variant = $this->productVariantRepository->findOneByCode($code);
        if (null !== $variant && $variant->isEnabled()) {
            $availabilityDate = null;
            foreach ($variant->getShippingStock() as $stock) {
                if (null === $availabilityDate || $availabilityDate > $stock->getShippingDate()) {
                    $availabilityDate = $stock->getShippingDate();
                }
            }

            $this->variantService->setRequest($calendarAvailabilityRequest);
            // TODO: Think about warehouse condition implementation
            $availabilityDate = $this->variantService->checkLogisticVariants(
                $code,
                $availabilityDate ?: new \DateTime(),
                new \DateTime()
            );
            $maxDateAvailable[$code] = $availabilityDate;
        } else {
            $maxDateAvailable[$code] = false;
        }
        try {
            $response = new Response(json_encode($maxDateAvailable));
            $response->setSharedMaxAge(60);
            $response->headers->addCacheControlDirective('must-revalidate', true);

            return $response;
        } catch (RequestException $exception) {
            if (null !== $exception->getResponse()) {
                throw new HttpException($exception->getResponse()->getStatusCode(), $exception->getResponse()->getReasonPhrase());
            }

            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }
}
