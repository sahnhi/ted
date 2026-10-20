<?php

declare(strict_types=1);

namespace App\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        $paths = new Paths();

        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            if (!str_starts_with($path, '/api/v2/admin/')) {
                $paths->addPath($path, $pathItem);
            }
        }

        return $openApi->withPaths($paths);
    }
}
