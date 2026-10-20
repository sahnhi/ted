<?php

namespace App\Controller\Api\Product;

use App\ViewRepository\ProductViewRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ShowAction
{
    public function __construct(
        private readonly ProductViewRepositoryInterface $productViewRepository,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(string $channelCode, string $code, string $localeCode): Response
    {
        try {
            $context = new Context();
            $context->setGroups(['product.show']);

            $view = View::create($this->productViewRepository->findOneByCode($channelCode, $code, $localeCode, withVariants: true), Response::HTTP_OK);
            $view->setContext($context);

            $response = $this->viewHandler->handle($view);
            $response->setSharedMaxAge(600);
            $response->headers->addCacheControlDirective('must-revalidate', true);

            return $response;
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
