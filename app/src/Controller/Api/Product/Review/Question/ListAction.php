<?php

namespace App\Controller\Api\Product\Review\Question;

use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use App\ViewRepository\ProductViewRepositoryInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ListAction
{
    public function __construct(
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly OrderRepository $cartRepository,
        private readonly ViewHandlerInterface $viewHandler,
        private readonly ProductViewRepositoryInterface $productViewRepository
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $context = new Context();
        $productCodes = [];
        $questionsAnswered = [];
        $token = $request->attributes->get('token');
        $channelCode = $request->attributes->get('channelCode');
        $localeCode = $request->attributes->get('localeCode');

        if ($token) {
            $cart = $this->cartRepository->findOneBy(['tokenValue' => $token]);
            foreach ($cart->getItems() as $item) {
                $productCodes[] = $item->getProduct()->getCode();
                if (null !== $item->getVariant()?->getBundleAssociations()) {
                    foreach ($item->getVariant()->getBundleAssociations() as $association) {
                        $productCodes[] = $association->getVariant()->getProduct()->getCode();
                    }
                }
            }

            $productReviews = $this->productReviewRepository->findBy(['order' => $cart]);

            foreach ($productReviews as $productReview) {
                foreach ($productReview->getAnswers() as $answer) {
                    $questionsAnswered[] = $answer->getQuestion()->getId();
                }
            }
        }

        $context->setGroups(['product_review_question.list']);
        $productListReviewQuestionList = $this->productViewRepository->findAllWithReviewRequested(
            $channelCode,
            $localeCode,
            $productCodes,
            $questionsAnswered
        );

        $view = View::create($productListReviewQuestionList, Response::HTTP_OK);
        $view->setContext($context);

        return $this->viewHandler->handle($view);
    }
}
