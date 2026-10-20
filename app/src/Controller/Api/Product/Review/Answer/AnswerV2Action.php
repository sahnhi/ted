<?php

namespace App\Controller\Api\Product\Review\Answer;

use App\Doctrine\Entity\ProductReviewAnswer;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductReview;
use App\Doctrine\Repository\Sylius\TaxonRepository;
use App\Model\TaxonReferenceInterface;
use App\Services\Drip\DripApiInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\ProductReviewTransitions;
use Sylius\Component\Resource\Factory\Factory;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Review\Factory\ReviewFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class AnswerV2Action
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly ReviewFactory $productReviewFactory,
        private readonly RepositoryInterface $customerRepository,
        private readonly Factory $customerFactory,
        private readonly RepositoryInterface $productRepository,
        private readonly RepositoryInterface $productReviewQuestionRepository,
        private readonly EntityManagerInterface $productReviewManager,
        private readonly EntityManagerInterface $customerManager,
        private readonly FactoryInterface $stateMachineFactory,
        private readonly ViewHandlerInterface $viewHandler,
        private readonly DripApiInterface $dripApi,
        private readonly RepositoryInterface $productReviewAnswerChoicesRepository,
        private readonly TaxonRepository $taxonRepository
    ) {
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws SMException
     */
    public function __invoke(Request $request): Response
    {
        $customer = $this->customerRepository->findOneByEmail($request->request->get('customer')['email']);

        $cartToken = $request->request->get('cartToken');
        $order = $this->orderRepository->getCompletedOrderByCartToken($cartToken);

        if (!$customer) {
            $customer = $this->customerFactory->createNew();
            $customer->setEmail($request->request->get('customer')['email']);
        } elseif (!$cartToken || !$order) {
            $order = $this->orderRepository->getOrderByCustomerEmailOrProducts($customer, $request->request->get('products'), true);
        }

        if ('' !== $request->request->get('customer')['birthday']) {
            $customer->setBirthday(\DateTime::createFromFormat('Y-m-d', $request->request->get('customer')['birthday']));
        }

        $customer->setFirstName($request->request->get('customer')['firstName']);
        $customer->setLocale($request->get('localeCode'));

        $this->customerManager->persist($customer);
        $requestProducts = $request->request->get('products');
        $productsCode = array_keys($requestProducts);
        $products = $this->productRepository->findAllByCode($productsCode);
        $repossessTaxon = $this->taxonRepository->findOneBy(['code' => TaxonReferenceInterface::REPOSSESS_REF]);

        foreach ($products as $product) {
            /** @var Product $product */
            $result = $requestProducts[$product->getCode()];
            $comment = $result['comment'];
            $rating = $result['rating'];
            unset($result['rating'], $result['comment']);
            Assert::notNull($product, 'Product with given code does not exists');

            $lengthMax = 40;
            if (strlen((string) $comment) > $lengthMax) {
                while (!preg_match('/^[a-z]$/i', (string) $comment[$lengthMax])) {
                    --$lengthMax;
                }
            } else {
                $lengthMax = strlen((string) $comment);
            }

            /** @var ProductReview $review */
            $review = $this->productReviewFactory->createNew();

            $review->setRating($rating);
            $review->setComment($comment);
            $review->setReviewSubject($product);
            $review->setAuthor($customer);
            $review->setLocale($request->get('localeCode'));

            $review->setTitle(substr((string) $comment, 0, $lengthMax));
            if ($order) {
                $review->setOrder($order);

                if (count($order->getShipments()) > 0 && $product->hasTaxon($repossessTaxon)) {
                    $review->setShippingMethod($order->getShipments()->first()->getMethod());
                }

                if ($product->hasTaxon($repossessTaxon) || $review->getRating() >= 4) {
                    $stateMachine = $this->stateMachineFactory->get($review, ProductReviewTransitions::GRAPH);
                    $stateMachine->apply(ProductReviewTransitions::TRANSITION_ACCEPT);
                }
            }

            foreach ($result as $resultQuestion => $resultAnswer) {
                $question = $this->productReviewQuestionRepository->findOneBy(
                    [
                        'question' => $resultQuestion,
                        'product' => $product,
                    ]
                );
                $answersId = explode(',', (string) $resultAnswer);
                foreach ($answersId as $answerId) {
                    $answer = $this->productReviewAnswerChoicesRepository->findOneById($answerId);
                    if (null !== $answer) {
                        if ('rating' === $question->getQuestionType()) {
                            $answerCode = $answerId;
                        } else {
                            $answerCode = $answer->getCode();
                        }
                        $productReviewAnswer = new ProductReviewAnswer();
                        $productReviewAnswer->setQuestion($question);
                        $productReviewAnswer->setAnswer($answerCode);
                        $productReviewAnswer->setReview($review);
                        $productReviewAnswer->setAnswerChoices($answer);
                        $review->addAnswer($productReviewAnswer);
                    }
                }
            }

            $this->productReviewManager->persist($review);

            $this->dripApi->sendEvent([
                [
                    'email' => $review->getAuthor()->getEmail(),
                    'action' => sprintf('Left a %s star review on %s', $review->getRating(), $review->getReviewSubject()->getCode()),
                ],
            ]);
        }

        $this->customerManager->flush();
        $this->productReviewManager->flush();

        return $this->viewHandler->handle(View::create([
            'nps' => (isset($order) && null === $order->getNPS() ? true : false),
        ]));
    }
}
