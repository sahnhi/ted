<?php

declare(strict_types=1);

namespace App\ApiPlatform\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Bundle\ApiBundle\Serializer\ContextKeys;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class OrderMethodsItemExtensionDecorator implements QueryItemExtensionInterface
{
    public function __construct(
        private readonly UserContextInterface $userContext
    ) {
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?string $operationName = null,
        array $context = []
    ) {
        if (!is_a($resourceClass, OrderInterface::class, true)) {
            return;
        }

        $httpRequestMethodType = $context[ContextKeys::HTTP_REQUEST_METHOD_TYPE];
        if (Request::METHOD_GET === $httpRequestMethodType) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $user = $this->userContext->getUser();

        if (null === $user) {
            $this->applyForVisitor($queryBuilder, $rootAlias, $operationName);

            return;
        }

        if ($user instanceof ShopUserInterface && in_array('ROLE_USER', $user->getRoles(), true)) {
            $this->applyForShopUser($queryBuilder, $rootAlias, $operationName, $user);

            return;
        }

        if (
            $user instanceof AdminUserInterface && in_array('ROLE_API_ACCESS', $user->getRoles(), true)) {
            $this->applyForAdminUser($queryBuilder, $rootAlias, $httpRequestMethodType);

            return;
        }

        throw new AccessDeniedHttpException('Requested method is not allowed.');
    }

    private function applyForVisitor(QueryBuilder $queryBuilder, string $rootAlias, string $operationName): void
    {
        $queryBuilder
            ->leftJoin(sprintf('%s.customer', $rootAlias), 'customer')
            ->leftJoin('customer.user', 'user')
            ->andWhere($queryBuilder->expr()->orX('user IS NULL', sprintf('%s.customer IS NULL', $rootAlias)));

        if ('shop_select_payment_method' !== $operationName && 'shop_nps_review' !== $operationName) {
            $this->filterCart($queryBuilder, $rootAlias);
        }
    }

    private function applyForShopUser(
        QueryBuilder $queryBuilder,
        string $rootAlias,
        string $operationName,
        ShopUserInterface $user
    ): void {
        $queryBuilder
            ->andWhere(sprintf('%s.customer = :customer', $rootAlias))
            ->setParameter('customer', $user->getCustomer()->getId());

        if (
            'shop_select_payment_method' !== $operationName
            && 'shop_account_change_payment_method' !== $operationName
        ) {
            $this->filterCart($queryBuilder, $rootAlias);
        }
    }

    private function applyForAdminUser(QueryBuilder $queryBuilder, string $rootAlias, string $httpRequestMethodType): void
    {
        if (Request::METHOD_DELETE === $httpRequestMethodType) {
            $this->filterCart($queryBuilder, $rootAlias);
        }
    }

    private function filterCart(QueryBuilder $queryBuilder, string $rootAlias): void
    {
        $queryBuilder
            ->andWhere(sprintf('%s.state = :state', $rootAlias))
            ->setParameter('state', OrderInterface::STATE_CART);
    }
}
