<?php

namespace App\Legacy\Dalenys;

use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class DalenysFileHandler
{
    public const EXTRACT_FOLDER = '/src/Resources/private/CSV/Dalenys';
    public const DALENYS_PAYMENT_METHOD = 'dalenys';
    public const STORE_ID_PARIS = 5;

    public const STORE_NAME_PARIS = 'TEDIBER - Paris';
    public const STORE_NAME_TOULOUSE = 'TEDIBER - Toulouse';
    public const STORE_NAME_LILLE = 'TEDIBER - Lille';

    public const DALENYS_STORES = [self::STORE_NAME_PARIS, self::STORE_NAME_TOULOUSE, self::STORE_NAME_LILLE];

    public function __construct(
        private readonly string $projectDir,
        private readonly RepositoryInterface $paymentRepository,
        private readonly RepositoryInterface $paymentMethodRepository,
        private readonly MailerInterface $mailer
    ) {
    }

    public function handlePaymentsFile(UploadedFile $file)
    {
        if ('application/zip' === $file->getMimeType()) {
            $zip = new \ZipArchive();
            $name = str_replace('.zip', '', $file->getClientOriginalName());
            $path = $this->projectDir . self::EXTRACT_FOLDER;

            if ($zip->open($file->getRealPath())) {
                $zip->extractTo($path);
                $zip->close();
                unlink($file->getRealPath());

                if (file_exists($path . '/' . $name)) {
                    $rows = [];
                    if (($handle = fopen($path . '/' . $name, 'rb')) !== false) {
                        while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                            $rows[] = $data;
                        }
                        fclose($handle);
                    }

                    if (!empty($rows)) {
                        $this->readPayments($rows);
                    }

                    unlink($path . '/' . $name);
                }
            }
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function readPayments(array $rows)
    {
        $keys = array_shift($rows);

        // BOM problem
        $keys[0] = 'IDENTIFIER';

        // Limit to Paris untill we have an option for other stores
        $payments = empty($rows) ? [] : $this->paymentRepository->findStorePaymentsOfDay(self::STORE_ID_PARIS);
        $interval2H = new \DateInterval('PT2H');
        $interval15M = new \DateInterval('PT15M');
        $paymentMethod = $this->paymentMethodRepository->findOneByCode(self::DALENYS_PAYMENT_METHOD);

        foreach ($rows as $row) {
            $identifier = $row[0];
            $statusCode = $row[8];

            if (!in_array($identifier, self::DALENYS_STORES) || '0000' !== $statusCode) {
                continue;
            }

            $date = new \DateTime($row[1]);
            $date->sub($interval2H);
            $dateEnd = clone $date;
            $dateEnd->add($interval15M);

            $matchedPayments = array_filter(
                $payments,
                fn (Payment $payment): bool => $payment->getOrder()->getCheckoutCompletedAt() >= $date
                    && $payment->getOrder()->getCheckoutCompletedAt() <= $dateEnd
                    && (float) $payment->getAmount() / 100 === (float) $row[5]
            );

            if (empty($matchedPayments)) {
                continue;
            }

            if (count($matchedPayments) > 1) {
                $body = '';
                foreach ($matchedPayments as $matchedPayment) {
                    $body .= $matchedPayment->getId() . ' => Commande n°' . $matchedPayment->getOrder()->getNumber() . '<br />';
                }

                $message = (new Email())
                    ->subject('IMPOSSIBLE de matcher la transaction boutique ' . $row[2] . ' automatiquement')
                    ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                    ->to('dev@tediber.com')
                    ->html(
                        'Le TRANSACTIONID Dalenys n°' . $row[2] . ' n\'a pas pu être attaché automatiquement à sa commande Sylius. <br />' .
                        'Plus d\'une occurence a été trouvée lors de la recherche du paiement correspondant : <br />' .
                        $body,
                    );

                $this->mailer->send($message);
            } else {
                $details = [];
                foreach ($keys as $k => $key) {
                    $details[$key] = $row[$k];
                }

                $payment = current($matchedPayments);
                $payment->setDetails($details);
                $payment->setMethod($paymentMethod);
                $this->paymentRepository->add($payment);
            }
        }
    }
}
