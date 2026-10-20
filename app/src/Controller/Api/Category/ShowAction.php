<?php

namespace App\Controller\Api\Category;

use App\ViewRepository\TaxonViewRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ShowAction
{
    public function __construct(
        private readonly TaxonViewRepositoryInterface $taxonViewRepository,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(string $channelCode, string $localeCode, string $taxonSlug): Response
    {
        try {
            $view = View::create($this->taxonViewRepository->findOneBySlug($channelCode, $taxonSlug, $localeCode));

            $response = $this->viewHandler->handle($view);
            $response->setSharedMaxAge(600);
            $response->headers->addCacheControlDirective('must-revalidate', true);

            return $response;
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
