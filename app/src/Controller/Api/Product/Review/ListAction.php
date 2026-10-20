<?php

namespace App\Controller\Api\Product\Review;

use App\Doctrine\Repository\ProductReviewQuestionRepository;
use App\Doctrine\Repository\Sylius\ProductRepository;
use App\Model\PaginatorDetails;
use App\ViewRepository\ProductReviewViewRepositoryInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class ListAction
{
    private readonly Request $request;

    public function __construct(
        private readonly ProductReviewViewRepositoryInterface $productReviewViewRepository,
        private readonly ProductReviewQuestionRepository $productReviewQuestionRepository,
        private readonly ProductRepository $productRepository,
        private readonly ViewHandlerInterface $viewHandler,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function __invoke(string $channelCode, string $localeCode, string $code): Response
    {
        $questionsParameters = $this->request->query->all();
        $productCode = $code;
        $product = $this->productRepository->findOneBy(['code' => $productCode]);

        $allQuestions = [];
        foreach ($questionsParameters as $questionParameter => $answerParameter) {
            $questionObject = $this->productReviewQuestionRepository->findOneBy(['product' => $product, 'question' => $questionParameter]);

            if ($questionObject) {
                $allQuestions[] = ['question' => $questionObject->getQuestion(), 'answer' => $answerParameter];
            }
        }
        $filter = [
            'rating' => $this->request->query->get('rating'),
            'minRating' => $this->request->query->get('minRating'),
            'star' => $this->request->query->get('star'),
            'ageMin' => $this->request->query->get('ageMin'),
            'ageMax' => $this->request->query->get('ageMax'),
            'comments' => $this->request->query->get('comments') ?: false,
        ];

        $page = $this->productReviewViewRepository->getByProductCode(
            $productCode,
            $this->request->attributes->get('channelCode'),
            new PaginatorDetails($this->request->attributes->get('_route'), array_merge([
                'channelCode' => $channelCode,
                'localeCode' => $localeCode,
            ], $this->request->query->all())),
            $localeCode,
            $filter,
            $allQuestions
        );

        $context = new Context();
        $context->setGroups(['product_review.list']);

        $view = View::create($page, Response::HTTP_OK);
        $view->setContext($context);
        /* http cache not working properly with get parameters */
        // $response->setSharedMaxAge(60);
        // $response->headers->addCacheControlDirective('must-revalidate', true);

        return $this->viewHandler->handle($view);
    }
}
