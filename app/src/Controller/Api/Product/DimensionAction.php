<?php

namespace App\Controller\Api\Product;

use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DimensionAction
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository
    ) {
    }

    public function __invoke(Request $request, string $codes): Response
    {
        $widths = null !== $request->get('widths') ? explode(',', (string) $request->get('widths')) : null;
        $lengths = null !== $request->get('lengths') ? explode(',', (string) $request->get('lengths')) : null;

        $productCodes = explode(',', $codes);
        $dimensions = [];

        foreach ($productCodes as $code) {
            $dimensions[$code] = $this->productVariantRepository
                ->findAllByDimensionsAndProductCode($code, $widths, $lengths);
        }

        return new JsonResponse($dimensions);
    }
}
