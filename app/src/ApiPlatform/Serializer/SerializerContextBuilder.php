<?php

declare(strict_types=1);

namespace App\ApiPlatform\Serializer;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

class SerializerContextBuilder implements SerializerContextBuilderInterface
{
    public const QUERY_PARAMS_KEY = 'app_query_params';

    public function __construct(
        private readonly SerializerContextBuilderInterface $decoratedSerializerContextBuilder
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decoratedSerializerContextBuilder->createFromRequest($request, $normalization, $extractedAttributes);

        // Since we cannot access the applied APIP filter during the serialization process, we pass the query params to
        // the serializer context as a workaround
        $context[self::QUERY_PARAMS_KEY] = $request->query->all();

        return $context;
    }
}
