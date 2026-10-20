<?php

namespace App\Command\Cron\Review;

use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use App\Services\Admin\Export\ExportCsv;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class ReviewCsvCommand extends Command
{
    public const HEADER = [
        'Fullname',
        'Shipping street',
        'Shipping city',
        'Shipping country',
    ];

    protected static $defaultName = 'app:cron:review:csv';

    public function __construct(
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ExportCsv $exportCsv,
        private readonly MailerInterface $mailer,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Review CSV');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $d = new \DateTime();
        $reviews = [
            $this->productReviewRepository->getNoRemercieAndAccepted(null, 'iterate'),
        ];

        foreach ($reviews as $review) {
            $data = [self::HEADER];
            while (false !== ($row = $review->next())) {
                $order = $row[0]->getOrder();
                $data[] = [
                    $this->cleanText($row[0]->getAuthor()->getFullName()),
                    $this->cleanText(null !== $order ? $order->getShippingAddress()->getStreet() : ''),
                    null !== $order ? $this->cleanText($order->getShippingAddress()->getPostcode()) . ' ' . $this->cleanText($order->getShippingAddress()->getCity()) : '',
                    $this->cleanText(null !== $order ? $order->getShippingAddress()->getCountryCode() : ''),
                ];

                $this->entityManager->detach($row[0]);
            }

            if (count($data) > 1) {
                $content = $this->exportCsv->contentFormatCSV($data);
            }
        }

        $this->productReviewRepository->updateNoRemercieAndAccepted([]);

        if (!empty($content)) {
            $message = (new Email())
                ->subject('Export CSV remercie')
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::THANKS_EXPORT)->getPrincipals())
                ->text('Export CSV');

            $message->attach($content, $d->format('Y-m-d'), 'text/csv');
            $this->mailer->send($message);
        }

        return 0;
    }

    private function cleanText($string): string
    {
        return str_replace("\n", ' ', trim((string) $string));
    }
}
