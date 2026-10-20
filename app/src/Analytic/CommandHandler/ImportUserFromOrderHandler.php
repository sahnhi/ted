<?php

declare(strict_types=1);

namespace App\Analytic\CommandHandler;

use App\Analytic\Client\AnalyticClient;
use App\Analytic\Command\ImportUserFromOrder;
use App\Analytic\Model\User;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Services\Sentry\ReportingInterface;
use App\Utils\PhoneNumber;
use Psr\Log\LoggerInterface;
use Sentry\Severity;
use Sylius\Component\Core\Model\AddressInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ImportUserFromOrderHandler implements MessageHandlerInterface
{
    private const DEFAULT_COUNTRY = 'fr';

    public function __construct(
        private readonly AnalyticClient $analyticClient,
        private readonly PhoneNumber $phoneNumber,
        private readonly LoggerInterface $rimdianLogger,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function __invoke(ImportUserFromOrder $command): void
    {
        try {
            $order = $command->getOrder();

            /** @var CustomerInterface $customer */
            $customer = $order->getCustomer();
            $address = $order->getBillingAddress();

            $this->rimdianLogger->info(sprintf('New Sylius -> Rimdian import handler single user with id %s, from order number %s', $customer->getId(), $order->getNumber()));

            $user = new User(
                (string) $customer->getId(),
                $customer->getCreatedAt(),
                $customer->getCreatedAt(),
                $customer->getLocale(),
                $address instanceof AddressInterface ? $address->getCountryCode() : self::DEFAULT_COUNTRY,
                $customer->getFirstName(),
                $customer->getLastName(),
                trim((string) $customer->getEmail()),
                $customer->getBirthday(),
                $customer->getPhoneNumber() ? $this->phoneNumber->formatInE164($customer->getPhoneNumber()) : null,
                $address instanceof AddressInterface ? $address->getStreet() : null,
                $address instanceof AddressInterface ? $address->getCity() : null,
                $address instanceof AddressInterface ? $address->getPostcode() : null
            );

            $this->analyticClient->sendUser($user);
        } catch (\Throwable $e) {
            $this->reporting->reportIssue(
                $e,
                Severity::error(),
                ['rimdian' => 'import_user_from_order'],
                ['payload' => (array) $command]
            );

            return;
        }

        $this->rimdianLogger->info(sprintf('Success -> Rimdian import single user with id %s, from order number %s', $customer->getId(), $order->getNumber()));
    }
}
