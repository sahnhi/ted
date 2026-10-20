<?php

declare(strict_types=1);

namespace App\Controller\Product;

use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends ResourceController
{
    public function searchProduct(Request $request): JsonResponse
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->get('sylius.repository.product');

        /** @var LocaleContextInterface $localeContext */
        $localeContext = $this->get('sylius.context.locale');

        $locale = $localeContext->getLocaleCode();

        /** @var array $criteria */
        $criteria = $request->query->get('criteria');
        $term = $criteria['search']['value'];

        return new JsonResponse([
            '_embedded' => [
                'items' => $productRepository->findBySearch($term, $locale),
            ],
        ]);
    }
}
