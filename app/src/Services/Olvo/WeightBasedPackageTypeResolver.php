<?php

declare(strict_types=1);

namespace App\Services\Olvo;

use App\Doctrine\Entity\Sylius\OrderItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeightBasedPackageTypeResolver implements PackageTypeResolverInterface
{
    private array|null $packageTypes = null;

    public function __construct(
        private readonly HttpClientInterface $olvoClient
    ) {
    }

    /**
     * @throws PackageTypeNotFoundException
     */
    public function resolvePackageType(OrderItem $orderItem): int
    {
        try {
            $packageTypes = $this->doGetPackageTypes();
        } catch (ExceptionInterface $e) {
            throw new PackageTypeNotFoundException(previous: $e);
        }

        if ([] === $packageTypes) {
            throw new PackageTypeNotFoundException('API returned no package types');
        }

        foreach ($packageTypes as $packageType) {
            if ($orderItem->getVariant()?->getWeight() <= $packageType['weight']) {
                return $packageType['id'];
            }
        }

        throw new PackageTypeNotFoundException('No suitable package type found');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function doGetPackageTypes(): array
    {
        if (null === $this->packageTypes) {
            $this->packageTypes = $this->olvoClient->request(Request::METHOD_GET, '/api/v2/package_types')->toArray();
        }

        return $this->packageTypes;
    }
}
