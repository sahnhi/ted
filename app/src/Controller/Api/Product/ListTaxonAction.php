<?php

namespace App\Controller\Api\Product;

use App\ViewRepository\TaxonViewRepositoryInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ListTaxonAction
{
    public function __construct(
        private readonly TaxonViewRepositoryInterface $taxonViewRepository,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    public function __invoke(string $channelCode): Response
    {
        try {
            $context = new Context();
            $context->setGroups(['taxon.list']);

            $view = View::create($this->taxonViewRepository->findAllByChannelAndLocale($channelCode, 'fr_FR'), Response::HTTP_OK);
            $view->setContext($context);

            $response = $this->viewHandler->handle($view);

            $response->setSharedMaxAge(600);
            $response->headers->addCacheControlDirective('must-revalidate');

            unset($view, $context);

            return $response;
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
