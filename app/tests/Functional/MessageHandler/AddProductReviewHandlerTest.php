<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Doctrine\Entity\ProductReviewAnswer;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Product;
use App\MessageHandler\ProductReview\AddProductReviewHandler;
use App\Model\ProductReview\AddProductReview;
use App\Services\Drip\DripApiInterface;
use Doctrine\ORM\EntityManagerInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddProductReviewHandlerTest extends KernelTestCase
{
    private readonly AddProductReviewHandler $handler;

    private readonly DripApiInterface $dripApi;

    private readonly EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->dripApi = $this->createMock(DripApiInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->handler = new AddProductReviewHandler(
            self::$container->get('App\MessageHandler\ProductReview\AddProductReviewHandler.inner'),
            self::$container->get('sylius.repository.order'),
            self::$container->get('sylius.repository.product_review_question'),
            self::$container->get('sylius.repository.product_review_answer_choices'),
            self::$container->get('sylius.factory.product_review_answer'),
            $this->entityManager,
            self::$container->get(StateMachineFactoryInterface::class),
            self::$container->get(LocaleContextInterface::class),
            $this->dripApi
        );
    }

    /**
     * @throws \SM\SMException
     *
     * @dataProvider provideAddProductReviewMessage
     */
    public function testItCorrectlyHandlesTheAddProductReviewMessage(AddProductReview $message, string $expectedStatus): void
    {
        $this->dripApi
            ->expects($this->once())
            ->method('sendEvent')
            ->with([[
                'email' => 'willBeCreated@soon.com',
                'action' => sprintf('Left a %d star review on mattress', $message->rating),
            ]]);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $review = ($this->handler)($message);

        self::assertSame($message->title, $review->getTitle());
        self::assertSame($message->rating, $review->getRating());
        self::assertSame($message->comment, $review->getComment());
        self::assertSame($expectedStatus, $review->getStatus());

        /** @var Customer $author */
        $author = $review->getAuthor();
        self::assertInstanceOf(Customer::class, $author);
        self::assertNotNull($author->getId());
        self::assertSame('willBeCreated@soon.com', $author->getEmail());
        self::assertSame('Foo', $author->getFirstName());
        self::assertSame($message->birthDay, $author->getBirthday());

        self::assertCount(1, $review->getAnswers());

        /** @var ProductReviewAnswer $answer */
        $answer = $review->getAnswers()->first();
        self::assertSame('problemesAvants', $answer->getQuestion()->getQuestion());
        self::assertSame('backProblem', $answer->getAnswerChoices()->getCode());

        /** @var Product $product */
        $product = $review->getReviewSubject();
        self::assertInstanceOf(Product::class, $product);
        self::assertSame('mattress', $product->getCode());
    }

    private function provideAddProductReviewMessage(): \Generator
    {
        $message = new AddProductReview(
            'Title',
            1,
            'Good!',
            'mattress',
            'Foo',
            'Bar',
            '5811cf75-84a3-4673-9cc3-69755d1b72ab',
            ['problemesAvants' => ['backProblem']],
            new \DateTime(),
            'willBeCreated@soon.com'
        );

        yield [$message, 'new'];

        $acceptedMessage = new AddProductReview(
            'Title',
            4,
            'Good!',
            'mattress',
            'Foo',
            'Bar',
            '5811cf75-84a3-4673-9cc3-69755d1b72ab',
            ['problemesAvants' => ['backProblem']],
            new \DateTime(),
            'willBeCreated@soon.com'
        );

        yield [$acceptedMessage, 'accepted'];
    }
}
