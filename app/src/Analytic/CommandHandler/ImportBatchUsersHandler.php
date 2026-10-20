<?php

declare(strict_types=1);

namespace App\Analytic\CommandHandler;

use App\Analytic\Client\AnalyticClient;
use App\Analytic\Command\ImportBatchUsers;
use App\Analytic\Model\User;
use App\Analytic\Model\UsersList;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Repository\Sylius\CustomerRepositoryInterface;
use App\Services\Sentry\ReportingInterface;
use App\Utils\PhoneNumber;
use libphonenumber\NumberParseException;
use Psr\Log\LoggerInterface;
use Sentry;
use Sylius\Component\Core\Model\AddressInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ImportBatchUsersHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly AnalyticClient $analyticClient,
        private readonly PhoneNumber $phoneNumber,
        private readonly LoggerInterface $rimdianLogger,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function __invoke(ImportBatchUsers $command): void
    {
        $offset = $command->getOffset();
        $limit = $command->getNumberOfUsersToImport();

        $customersId = $this->customerRepository->findCustomerIds($offset, $limit);
        $batchCustomerIds = array_chunk($customersId, ImportBatchUsers::BATCH_SIZE);

        foreach ($batchCustomerIds as $usersId) {
            $userList = UsersList::empty();
            foreach ($usersId as $userId) {
                /** @var Customer $customer */
                $customer = $this->customerRepository->find($userId);
                $order = $customer->getOrders()->last();
                $address = false !== $order ? $order->getBillingAddress() : null;

                $country = 'FR';
                if ($address instanceof AddressInterface && $address->getCountryCode()) {
                    $country = $address->getCountryCode();
                }

                try {
                    $user = new User(
                        (string) $customer->getId(),
                        $customer->getCreatedAt(),
                        $customer->getCreatedAt(),
                        $customer->getLocale(),
                        $country,
                        $customer->getFirstName(),
                        $customer->getLastName(),
                        $customer->getEmail(),
                        $customer->getBirthday(),
                        $customer->getPhoneNumber() ? $this->phoneNumber->formatInE164($customer->getPhoneNumber()) : null,
                        $address instanceof AddressInterface ? $address->getStreet() : null,
                        $address instanceof AddressInterface ? $address->getCity() : null,
                        $address instanceof AddressInterface ? $address->getPostcode() : null
                    );
                } catch (NumberParseException $e) {
                    $this->reporting->reportIssue(
                        $e->getMessage(),
                        Sentry\Severity::error(),
                        ['rimdian' => 'ImportBatchUsersHandler:createUser'],
                        ['customerId' => $customer->getId()]
                    );

                    $this->rimdianLogger->info(sprintf('Rimdian user not imported, phone number is not valid got %s for userId %d', $customer->getPhoneNumber(), $customer->getId()));

                    continue;
                } catch (\InvalidArgumentException $e) {
                    $this->reporting->reportIssue(
                        $e->getMessage(),
                        Sentry\Severity::error(),
                        ['rimdian' => 'ImportBatchUsersHandler:createUser'],
                        ['customerId' => $customer->getId()]
                    );

                    $this->rimdianLogger->info(sprintf('Rimdian user not imported, argument is not valid got %s for userId %d', $e->getMessage(), $customer->getId()));

                    continue;
                }

                $userList = $userList->addToList($user);
            }

            try {
                $this->analyticClient->sendBatchUsers($userList);
            } catch (\Throwable $e) {
                $this->reporting->reportIssue(
                    $e->getMessage(),
                    Sentry\Severity::error(),
                    ['rimdian' => 'ImportBatchUserHandler:clientFailed'],
                );

                return;
            }

            $this->rimdianLogger->info(sprintf('Rimdian users imported: %s', implode(', ', $userList->getUserIds())));
        }
    }
}
