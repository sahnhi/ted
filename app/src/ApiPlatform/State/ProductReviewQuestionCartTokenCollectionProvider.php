<?php

declare(strict_types=1);

namespace App\ApiPlatform\State;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\ProductReviewQuestion;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\ProductReview;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\OrderItemInterface;

class ProductReviewQuestionCartTokenCollectionProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public const OPERATION_NAME = 'product_review_question_get_cart';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return ProductReviewQuestion::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): iterable
    {
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneByTokenValue($context['filters']['cartToken']);
        if (null === $order) {
            return [];
        }

        $productCodes = $order->getItems()->map(static fn (OrderItemInterface $item): ?string => $item->getProduct()->getCode());

        /** @var Collection<ProductReview> $productReviews */
        $productReviews = $this->productReviewRepository->findBy(['order' => $order]);

        $questionsAnswered = [];
        foreach ($productReviews as $productReview) {
            foreach ($productReview->getAnswers() as $answer) {
                $questionsAnswered[] = $answer->getQuestion()->getId();
            }
        }

        $productsWithReviewsRequested = $this->productRepository->findAllWithReviewRequested(
            $order->getLocaleCode(),
            $productCodes->toArray(),
            $questionsAnswered
        );

        $questions = [];
        foreach ($productsWithReviewsRequested as $product) {
            $questions[$product->getCode()] = $product->getEnabledProductReviewQuestions();
        }

        return $questions;
    }
}
