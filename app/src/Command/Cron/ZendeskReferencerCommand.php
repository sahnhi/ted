<?php

namespace App\Command\Cron;

use App\Doctrine\Entity\CustomerSupport;
use App\Doctrine\Entity\Sylius\Customer;
use App\Model\Zendesk\Source;
use App\Services\Sentry\ReportingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sentry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\API\HttpClient;

class ZendeskReferencerCommand extends Command
{
    public const URI = '/api/v2/tickets.json?page=%d&sort_by=created_at&sort_order=desc';

    protected static $defaultName = 'app:cron:zendesk-referencer';
    private readonly EntityRepository $customerSupportRepository;

    public function __construct(
        private readonly EntityManagerInterface $customerManager,
        private readonly RepositoryInterface $customerRepository,
        private readonly HttpClient $client,
        private readonly ReportingInterface $reporting
    ) {
        parent::__construct();
        $this->customerSupportRepository = $this->customerManager->getRepository(CustomerSupport::class);
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Zendesk Referencer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nextPage = true;
        $page = 0;
        $dateInterval = new \DateInterval('P1M');
        $date = (new \DateTime())->sub($dateInterval);

        while ($nextPage) {
            try {
                ++$page;
                $tickets = $this->client->get(sprintf(self::URI, $page));

                foreach ($tickets->tickets as $ticket) {
                    if (false === in_array($ticket->via->channel, Source::SUPPORTED_CHANNELS)) {
                        continue;
                    }

                    $referencedSupport = $this->customerSupportRepository->findOneBy(['supportId' => $ticket->id]);
                    if (null !== $referencedSupport) {
                        continue;
                    }

                    if ($date > \DateTime::createFromFormat('d/m/Y', $ticket->created_at)) {
                        $nextPage = false;
                    }

                    /** @var Customer $customer */
                    $customer = null;
                    if (false === property_exists($ticket, 'via')
                        || false === property_exists($ticket->via, 'channel')
                        || false === property_exists($ticket->via, 'source')
                        || false === property_exists($ticket->via->source, 'from')
                        || false === property_exists($ticket->via->source, 'to')) {
                        continue;
                    }
                    $via = $ticket->via;

                    switch ($via->channel) {
                        case Source::CHANNEL_MAIL:
                            $customer = $this->customerRepository->findOneBy(['email' => $via->source->from->address]);
                            break;
                        case Source::CHANNEL_VOICE:
                            $customer = $this->customerRepository->findOneBy(['phoneNumber' => $via->source->from->phone]);
                            if (null === $customer) {
                                $customer = $this->customerRepository->findOneBy(['phoneNumber' => $via->source->to->phone]);
                            }
                            break;
                    }

                    if (null === $customer) {
                        continue;
                    }

                    $referencedSupport = new CustomerSupport();
                    $referencedSupport->setCustomer($customer);
                    $referencedSupport->setSupportId($ticket->id);
                    $referencedSupport->setCreatedAt(\DateTime::createFromFormat(DATE_ATOM, $ticket->created_at));
                    $this->customerManager->persist($referencedSupport);
                }

                if (null === $tickets->next_page) {
                    $nextPage = false;
                }
            } catch (AuthException $exception) {
                $this->reporting->reportIssue(
                    $exception,
                    Sentry\Severity::error(),
                    ['zendesk' => $date]
                );
            }
        }

        $this->customerManager->flush();

        return 0;
    }
}
