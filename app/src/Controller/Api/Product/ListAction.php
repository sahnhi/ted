<?php

namespace App\Controller\Api\Product;

use App\ViewRepository\ProductViewRepositoryInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ListAction
{
    private readonly Request $request;

    public function __construct(
        private readonly ProductViewRepositoryInterface $productViewRepository,
        private readonly ViewHandlerInterface $viewHandler,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function __invoke(string $channelCode): Response
    {
        try {
            $context = new Context();
            $context->setGroups(['product.list']);

            $bounds = !is_null($this->request->query->get('bounds'));

            $view = View::create($this->productViewRepository->findAllByChannel($channelCode, $bounds), Response::HTTP_OK);
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
