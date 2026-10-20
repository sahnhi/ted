<?php

namespace App\Command\Cron\Review;

use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use App\Services\Admin\Export\ExportCsv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class ExportReviewCommand extends Command
{
    public const HEADER = [
        'Firstname',
        'Lastname',
        'Code',
        'Rating',
        'Created_at',
        'Comment',
    ];

    protected static $defaultName = 'app:cron:review:export';

    public function __construct(
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly ExportCsv $exportCsv,
        private readonly MailerInterface $mailer,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Cron Review Export');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $d = new \DateTime();
        $reviews = $this->productReviewRepository->getReviewForLastMonth($d);

        $newRow = [self::HEADER];
        foreach ($reviews as $review) {
            $newRow[] = [
                $review->getAuthor()->getFirstname(),
                $review->getAuthor()->getLastname(),
                $review->getReviewSubject()->getCode(),
                $review->getRating(),
                $review->getCreatedAt()->format('d/m/Y'),
                $review->getComment(),
            ];
        }
        if (count($newRow) > 1) {
            $content = $this->exportCsv->contentFormatCSV($newRow);
        }
        if (!empty($content)) {
            $message = (new Email())
                ->subject('Export CSV du mois')
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::REVIEW_EXPORT)->getPrincipals())
                ->text('Export CSV');

            $message->attach($content, $d->format('Y-m-d'), 'text/csv');

            $this->mailer->send($message);
        }

        return 0;
    }
}
