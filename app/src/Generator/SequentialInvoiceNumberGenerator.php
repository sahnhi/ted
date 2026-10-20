<?php

namespace App\Generator;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceSequenceInterface;

final class SequentialInvoiceNumberGenerator implements InvoiceNumberGenerator
{
    public function __construct(
        private readonly RepositoryInterface $sequenceRepository,
        private readonly FactoryInterface $sequenceFactory,
        private readonly EntityManagerInterface $sequenceManager,
        private readonly int $startNumber = 1,
        private readonly int $numberLength = 6
    ) {
    }

    public function generate($date): string
    {
        $invoiceIdentifierPrefix = 'F' . $date->format('Ym');

        /** @var InvoiceSequenceInterface $sequence */
        $sequence = $this->getSequence();

        $this->sequenceManager->lock($sequence, LockMode::OPTIMISTIC, $sequence->getVersion());

        $number = $this->generateNumber($sequence->getIndex());
        $sequence->incrementIndex();

        return $invoiceIdentifierPrefix . $number;
    }

    private function generateNumber(int $index): string
    {
        $number = $this->startNumber + $index;

        return str_pad((string) $number, $this->numberLength, '0', \STR_PAD_LEFT);
    }

    private function getSequence(): InvoiceSequenceInterface
    {
        /** @var InvoiceSequenceInterface $sequence */
        $sequence = $this->sequenceRepository->findOneBy([]);

        if (null !== $sequence) {
            return $sequence;
        }

        /** @var InvoiceSequenceInterface $sequence */
        $sequence = $this->sequenceFactory->createNew();
        $this->sequenceManager->persist($sequence);

        return $sequence;
    }
}
