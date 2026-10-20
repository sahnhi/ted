<?php

namespace App\Controller\Admin\Action;

use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Services\Mail\Mailer\ProductUpdatedAlert;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class ProductAlertAction extends AbstractController
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductUpdatedAlert $productUpdatedAlert,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $variantId = $request->get('variantId');
        $productId = $request->get('productId');
        Assert::notNull($productId);

        if (null === $variantId) {
            $product = $this->productRepository->find($productId);
            Assert::notNull($product);

            $type = ProductUpdatedAlert::TYPE_PRODUCT;
            $code = $product->getCode();
            $redirect = new RedirectResponse($this->urlGenerator->generate('sylius_admin_product_update', ['id' => $productId]));
        } else {
            $variant = $this->productVariantRepository->find($variantId);
            Assert::notNull($variant);

            $type = ProductUpdatedAlert::TYPE_VARIANT;
            $code = $variant->getCode();
            $redirect = new RedirectResponse($this->urlGenerator->generate('sylius_admin_product_variant_index', ['productId' => $variant->getProduct()->getId()]));
        }

        $user = $this->getUser();
        $this->productUpdatedAlert->generateEmail(
            $type,
            $code,
            $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getEmail() . ')'
        );

        return $redirect;
    }
}
