<?php

namespace App\Controller\Api\Product\Variant;

use App\Exception\OutOfStockException;
use App\ViewRepository\ProductVariantViewRepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

class ListPerOptionsAction
{
    public function __construct(
        private readonly ProductVariantViewRepositoryInterface $productVariantViewRepository,
        private readonly ViewHandlerInterface $viewHandler
    ) {
    }

    /**
     * @throws NonUniqueResultException
     */
    public function __invoke(Request $request, string $channelCode, string $productCode): Response
    {
        try {
            $options = json_decode($request->getContent(), true);

            try {
                $errorMessage = sprintf('You must pass an associative array of option code => value code.');
                Assert::isArray($options, $errorMessage);
            } catch (\InvalidArgumentException $exception) {
                throw new BadRequestHttpException($exception->getMessage());
            }

            $context = new Context();
            $context->setGroups(['product_variant.list']);

            $view = View::create($this->productVariantViewRepository->findAllPerOptions($channelCode, $productCode, $options), Response::HTTP_OK);
            $view->setContext($context);

            return $this->viewHandler->handle($view);
        } catch (\Webmozart\Assert\InvalidArgumentException $exception) {
            throw new OutOfStockException($exception->getMessage());
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
