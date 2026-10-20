<?php

namespace App\Controller\Admin\Action;

use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class SearchProductAndVariantAction
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RepositoryInterface $productRepository,
        private readonly ProductVariantRepository $variantRepository,
        private readonly LocaleContextInterface $locale
    ) {
    }

    public function __invoke(Request $request): Response
    {
        Assert::notEmpty($request->query->get('criteria'));
        $criteria = $request->query->get('criteria');

        Assert::notEmpty($criteria['search']);
        $search = $criteria['search'];

        $productCount = $this->productRepository->findNumberByCodeOrName($search['value'], $this->locale->getLocaleCode());

        $response = new RedirectResponse(
            $this->urlGenerator->generate('sylius_admin_product_index', ['criteria' => $criteria])
        );

        if ($productCount > 0) {
            return $response;
        }

        $variant = $this->variantRepository->findOneBySearch($search['value'], $this->locale->getLocaleCode());

        if (null !== $variant) {
            $index = str_contains((string) $variant->getCode(), (string) $search['value']) ? 'code' : 'name';
            $criteria[$index] = [
                'type' => 'contains',
                'value' => $search['value'],
            ];
            unset($criteria['search']);

            $response = new RedirectResponse(
                $this->urlGenerator->generate('sylius_admin_product_variant_index', [
                    'criteria' => $criteria,
                    'productId' => $variant->getProduct()->getId(),
                ])
            );
        }

        return $response;
    }
}
