<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Provider\ReturnProduct\ReturnProductProviderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

final class ProductReturnGenerationAction
{
    public function __construct(
        private readonly RepositoryInterface $productRepository,
        private readonly ReturnProductProviderInterface $returnProductProvider,
        private readonly RouterInterface $router
    ) {
    }

    public function __invoke(Request $request): Response
    {
        Assert::notNull($request->attributes->get('id'));
        $product = $request->attributes->get('id');

        /** @var ProductInterface $product */
        $product = $this->productRepository->find($product);

        Assert::notNull($product, 'Product not found');

        $this->returnProductProvider->provideUpdatedOrNew($product);

        return new RedirectResponse(
            $this->router->generate(
                'sylius_admin_product_update',
                [
                    'id' => $request->attributes->get('id'),
                ]
            )
        );
    }
}
