<?php

namespace App\Controller\Api\Cart;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Exception\Api\Cart\InvalidCouponCode;
use App\Factory\View\ValidationErrorViewFactoryInterface;
use App\Request\AddCouponRequest;
use App\View\CartSummaryView;
use App\ViewRepository\CartViewRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AddCouponAction
{
    public function __construct(
        private readonly CommandBus $bus,
        private readonly ValidatorInterface $validator,
        private readonly ValidationErrorViewFactoryInterface $validationErrorViewFactory,
        private readonly CartViewRepositoryInterface $cartQuery,
        private readonly RepositoryInterface $promotionCouponRepository,
        private readonly RepositoryInterface $productVariantRepository,
        private readonly ViewHandlerInterface $viewHandler,
        private readonly EntityManagerInterface $paymentManager,
        private readonly RepositoryInterface $paymentRepository,
        private readonly LockFactory $lockFactory,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $addCouponRequest = new AddCouponRequest($request);
        $validationResults = $this->validator->validate($addCouponRequest);

        if (0 === count($validationResults)) {
            $lock = $this->lockFactory->createLock($addCouponRequest->getCommand()->orderToken(), 3.0);
            $lock->acquire(true);
            $addCouponCommand = $addCouponRequest->getCommand();

            try {
                $this->bus->handle($addCouponCommand);
                $coupon = $this->promotionCouponRepository->findOneBy(['code' => $addCouponCommand->couponCode()]);

                $promotion = $coupon?->getPromotion();

                if (null !== $promotion && str_starts_with((string) $promotion->getCode(), 'nyan-cat')) {
                    $productVariantCodeGift = $promotion->getActions()[0]->getConfiguration()['default']['filters']['products_filter']['products'][0];

                    /** @var ProductVariant|null $productVariantGift */
                    $productVariantGift = $this->productVariantRepository->findOneBy(['code' => $productVariantCodeGift]);

                    $productCodeGift = $productVariantGift->getProduct()->getCode();

                    return $this->viewHandler->handle(
                        View::create(['productCode' => $productCodeGift, 'variantCode' => $productVariantCodeGift, 'quantity' => 1], Response::HTTP_OK)
                    );
                }

                $cart = $this->cartQuery->getOneByToken($addCouponCommand->orderToken());
                if (0 === $cart->totals->total) {
                    $this->fixPayments($cart);
                }

                return $this->viewHandler->handle(
                    View::create($cart, Response::HTTP_OK)
                );
            } catch (\InvalidArgumentException $exception) {
                throw new InvalidCouponCode($exception->getMessage());
            } finally {
                $lock->release();
            }
        }

        return $this->viewHandler->handle(View::create($this->validationErrorViewFactory->create($validationResults), Response::HTTP_BAD_REQUEST));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function fixPayments(CartSummaryView $cart)
    {
        $payments = $this->paymentRepository->findByOrderToken($cart->tokenValue);

        foreach ($payments as $payment) {
            $this->paymentManager->remove($payment);
        }

        $this->paymentManager->flush();
    }
}
