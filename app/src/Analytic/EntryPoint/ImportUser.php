<?php

declare(strict_types=1);

namespace App\Analytic\EntryPoint;

use App\Analytic\Command\ImportBatchUsers;
use App\Analytic\Command\ImportUserFromOrder;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Doctrine\Entity\Sylius\Order;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class ImportUser
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $rimdianLogger
    ) {
    }

    public function importBatchUsers(Request $request): Response
    {
        Assert::true(null !== $request->request->get('start'), 'missing argument : start');
        Assert::true(null !== $request->request->get('limit'), 'missing argument : limit');

        $offset = $request->request->get('start');
        $limit = min($request->request->get('limit'), 9990);

        $this->rimdianLogger->info(sprintf('New Sylius -> Rimdian import batch users from id %s to id %s', $offset, $limit));

        $this->bus->dispatch(new ImportBatchUsers($offset, $limit));

        return new Response(null, Response::HTTP_ACCEPTED);
    }

    public function sendUserFromOrder(Order $order): void
    {
        if (!$order->getNumber()) {
            return;
        }

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        $this->rimdianLogger->info(sprintf('New Sylius -> Rimdian import single user with id %s, from order number %s', $customer->getId(), $order->getNumber()));

        $this->bus->dispatch(new ImportUserFromOrder($order));
    }
}
