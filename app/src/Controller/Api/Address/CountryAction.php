<?php

namespace App\Controller\Api\Address;

use App\View\CountryView;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CountryAction
{
    public function __construct(
        private readonly RepositoryInterface $countryRepository,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $context = new Context();
            $context->setGroups(['country.list']);

            $countries = $this->countryRepository->findBy(['enabled' => true]);

            $countriesView = [];
            foreach ($countries as $country) {
                $countryView = new CountryView();
                $countryView->code = $country->getCode();
                $countryView->name = $country->getName($request->get('localeCode'));
                $countryView->paidReturn = $country->isPaidReturn();

                /* to delete all accents and sort
                $countryName = $country->getName($request->get('localeCode'));
                $countryView->name = htmlentities($countryName, ENT_NOQUOTES, 'utf-8');
                $countryView->name = preg_replace('#&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring);#', '\1', $countryView->name);
                $countryView->name = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $countryView->name);
                $countryView->name = preg_replace('#&[^;]+;#', '', $countryView->name);
                */

                $countriesView[] = $countryView;
            }

            sort($countriesView, SORT_LOCALE_STRING);

            $view = View::create($countriesView, Response::HTTP_OK);

            $response = $this->viewHandler->handle($view);
            $response->setSharedMaxAge(600);
            $response->headers->addCacheControlDirective('must-revalidate', true);

            return $response;
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
