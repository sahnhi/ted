<?php

namespace App\Controller\Api\Taxon\Review;

use App\Model\PaginatorDetails;
use App\ViewRepository\ProductReviewViewRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShowAction
{
    private readonly Request $request;

    public function __construct(
        private readonly ProductReviewViewRepositoryInterface $productReviewViewRepository,
        private readonly ViewHandlerInterface $viewHandler,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(string $channelCode, string $localeCode, string $taxonSlug): Response
    {
        try {
            $context = new Context();
            $context->setGroups(['category_review.show']);

            $filter = [
                'comments' => $this->request->query->get('comments') ?: false,
            ];

            $view = View::create($this->productReviewViewRepository->getReviewsByTaxon($taxonSlug, $channelCode, new PaginatorDetails($this->request->attributes->get('_route'), array_merge([
                'channelCode' => $channelCode,
                'localeCode' => $localeCode,
                'taxonSlug' => $taxonSlug,
            ], $this->request->query->all())), $localeCode, $filter), Response::HTTP_OK);
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
