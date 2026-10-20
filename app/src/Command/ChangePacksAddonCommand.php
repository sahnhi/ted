<?php

namespace App\Command;

use App\Doctrine\Repository\PackRepository;
use App\Doctrine\Repository\Sylius\ProductRepository;
use App\Services\Sentry\Reporting;
use Doctrine\ORM\EntityManagerInterface;
use Sentry\Severity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ChangePacksAddonCommand extends Command
{
    public function __construct(
        private readonly PackRepository $packRepository,
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Reporting $reporting
    ) {
        parent::__construct('app:change-packs-addon');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array $packs */
        $packs = $this->packRepository->findAllUpsell();
        foreach ($packs as $pack) {
            /* example pack upsell : upsell_mattress_160200 */
            $data = explode('_', (string) $pack->getCode());
            $product = $this->productRepository->findOneByCode($data[1]);
            if (!empty($product)) {
                foreach ($pack->getVariants() as $variant) {
                    if ($variant->getProduct()->getCode() !== $product->getCode()) {
                        $pack->addAddon($variant);
                        $pack->removeVariant($variant);
                    }
                }
            } else {
                $this->reporting->reportIssue(
                    'Product code not found',
                    Severity::error(),
                    [
                        'pack_code' => $pack->getCode(),
                        'product_code' => $data[1],
                    ]
                );
            }
            $this->entityManager->persist($pack);
        }
        $this->entityManager->flush();

        $output->writeln('Packs addon changed successfully');

        return 0;
    }

    protected function configure(): void
    {
        $this->setDescription('Change variant to addon for upsell packs');
    }
}
