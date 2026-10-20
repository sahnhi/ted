<?php

declare(strict_types=1);

namespace App\MessageHandler\ProductReview;

use App\Doctrine\Entity\ProductReviewAnswer;
use App\Doctrine\Entity\ProductReviewAnswerChoices;
use App\Doctrine\Entity\ProductReviewQuestion;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductReview;
use App\Doctrine\Repository\ProductReviewAnswerChoicesRepositoryInterface;
use App\Doctrine\Repository\ProductReviewQuestionRepositoryInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Model\ProductReview\AddProductReview;
use App\Model\TaxonReferenceInterface;
use App\Services\Drip\DripApiInterface;
use Doctrine\ORM\EntityManagerInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\ProductReviewTransitions;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddProductReviewHandler implements MessageHandlerInterface
{
    public const RATING_APPROVED_THRESHOLD = 4;

    public function __construct(
        private readonly MessageHandlerInterface $decoratedHandler,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductReviewQuestionRepositoryInterface $productReviewQuestionRepository,
        private readonly ProductReviewAnswerChoicesRepositoryInterface $productReviewAnswerChoicesRepository,
        private readonly FactoryInterface $productReviewAnswerFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly StateMachineFactoryInterface $stateMachineFactory,
        private readonly LocaleContextInterface $localeContext,
        private readonly DripApiInterface $dripApi,
    ) {
    }

    /**
     * @throws SMException
     */
    public function __invoke(AddProductReview $addProductReview): ProductReview
    {
        $locale = $this->localeContext->getLocaleCode();

        /** @var ProductReview $review */
        $review = ($this->decoratedHandler)($addProductReview);
        $review->setLocale($locale);

        /** @var Customer $customer */
        $customer = $review->getAuthor();
        $customer->setFirstName($addProductReview->firstName);
        $customer->setLastName($addProductReview->lastName);
        $customer->setBirthday($addProductReview->birthDay);
        $customer->setLocale($locale);

        $this->entityManager->persist($customer);

        /** @var Product $product */
        $product = $review->getReviewSubject();

        /** @var Order $order */
        $order = $this->orderRepository->findOneByTokenValue($addProductReview->cartToken);
        if (null !== $order) {
            $review->setOrder($order);

            $hasRepossess = !$product->getTaxons()
                ->filter(static fn (TaxonInterface $taxon): bool => TaxonReferenceInterface::REPOSSESS_REF === $taxon->getCode())
                ->isEmpty();

            if ($hasRepossess && !$order->getShipments()->isEmpty()) {
                $review->setShippingMethod($order->getShipments()->first()->getMethod());
            }

            if ($hasRepossess || $review->getRating() >= self::RATING_APPROVED_THRESHOLD) {
                $stateMachine = $this->stateMachineFactory->get($review, ProductReviewTransitions::GRAPH);
                if ($stateMachine->can(ProductReviewTransitions::TRANSITION_ACCEPT)) {
                    $stateMachine->apply(ProductReviewTransitions::TRANSITION_ACCEPT);
                }
            }
        }

        foreach ($addProductReview->answers as $questionCode => $answerChoicesCodes) {
            /** @var ProductReviewQuestion|null $question */
            $question = $this->productReviewQuestionRepository->findOneBy([
                'question' => $questionCode,
                'product' => $product,
            ]);

            if (null === $question) {
                continue;
            }

            foreach ($answerChoicesCodes as $answerChoicesCode) {
                /** @var ProductReviewAnswerChoices|null $answerChoices */
                $answerChoices = $this->productReviewAnswerChoicesRepository->findOneBy(['code' => $answerChoicesCode]);
                if (null === $answerChoices) {
                    continue;
                }

                /** @var ProductReviewAnswer $productReviewAnswer */
                $productReviewAnswer = $this->productReviewAnswerFactory->createNew();
                $productReviewAnswer->setQuestion($question);
                $productReviewAnswer->setAnswer(ProductReviewQuestion::TYPE_RATING === $question->getQuestionType() ? $answerChoicesCode : $answerChoices->getCode());
                $productReviewAnswer->setReview($review);
                $productReviewAnswer->setAnswerChoices($answerChoices);

                $review->addAnswer($productReviewAnswer);
            }
        }

        $this->entityManager->persist($review);
        $this->entityManager->flush();

        $this->dripApi->sendEvent([[
            'email' => $review->getAuthor()->getEmail(),
            'action' => sprintf('Left a %s star review on %s', $review->getRating(), $product->getCode()),
        ]]);

        return $review;
    }
}
