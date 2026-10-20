<?php

declare(strict_types=1);

namespace App\ApiPlatform\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $decorated
    ) {
    }

    public function normalize($object, $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        foreach ($docs['paths'] as $path => $pathItem) {
            if (str_starts_with((string) $path, '/api/v2/admin/')) {
                unset($docs['paths'][$path]);
            }

            if ('/api/v2/shop/product-reviews/{id}' === $path) {
                unset($docs['paths'][$path]);
            }
        }

        return $docs;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
