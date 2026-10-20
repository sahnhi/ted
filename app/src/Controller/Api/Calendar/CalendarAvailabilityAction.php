<?php

namespace App\Controller\Api\Calendar;

use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Exception\ProductVariantNotFound;
use App\Factory\Calendar\Request\CalendarAvailabilityRequestFactory;
use App\Factory\View\ValidationErrorViewFactoryInterface;
use App\Services\Calendar\CalendarApi;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CalendarAvailabilityAction
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorViewFactoryInterface $validationErrorViewFactory,
        private readonly ViewHandlerInterface $viewHandler,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly CalendarApi $calendarApiService,
        private readonly CalendarAvailabilityRequestFactory $calendarAvailabilityRequestFactory,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): Response
    {
        $calendarAvailabilityRequest = $this->calendarAvailabilityRequestFactory->createFromRequest($request);

        $validationResults = $this->validator->validate($calendarAvailabilityRequest);

        if (0 !== count($validationResults)) {
            return $this->viewHandler->handle(View::create($this->validationErrorViewFactory->create($validationResults), Response::HTTP_BAD_REQUEST));
        }

        $lastDate = $calendarAvailabilityRequest->getDate();

        foreach ($calendarAvailabilityRequest->getProducts() as $product) {
            $lastDate = $calendarAvailabilityRequest->isWithoutStock() ?
                $lastDate :
                $this->productVariantRepository->getFirstAvailableShippingDate($product['code'], $lastDate);
        }

        $calendarAvailabilityRequest->setDate($lastDate);

        try {
            $this->calendarApiService->setRequest($calendarAvailabilityRequest);

            return new JsonResponse($this->calendarApiService->checkCalendar());
        } catch (RequestException $exception) {
            if (null !== $exception->getResponse()) {
                throw new HttpException($exception->getResponse()->getStatusCode(), $exception->getResponse()->getReasonPhrase());
            }

            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
        } catch (ProductVariantNotFound $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
