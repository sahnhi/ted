<?php

namespace App\Command\Ecotax;

use App\Doctrine\Entity\ProductVariantEcotax;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InsertReturnAndPhenixEcotaxCommand extends Command
{
    protected static $defaultName = 'app:ecotax:insert-return-phenix';

    public function __construct(
        private readonly RepositoryInterface $productVariantEcotaxRepository,
        private readonly RepositoryInterface $productVariantRepository
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Insert ecotax for return and phenix product');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productVariantEcotaxes = $this->productVariantEcotaxRepository->findAll();
        foreach ($productVariantEcotaxes as $productVariantEcotax) {
            $phenixVariant = null;
            $code = $productVariantEcotax->getVariant()->getCode();
            /* Product Variant Return */
            $returnVariant = $this->productVariantRepository->findOneBy(['code' => 'R' . $code]);
            if (null !== $returnVariant) {
                $newInsert = new ProductVariantEcotax();
                $newInsert->setEcotax($productVariantEcotax->getEcotax());
                $newInsert->setVariant($returnVariant);
                $newInsert->setManufacturerPaid(ProductVariantEcotax::MANUFACTURER_RETURN);
                $this->productVariantEcotaxRepository->add($newInsert);
            }
            /* Product Variant Phenix */
            $pattern = "/(\d.*)/";
            $array = preg_split($pattern, (string) $code, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            if (isset($array[0]) && isset($array[1])) {
                $phenixVariant = $this->productVariantRepository->findOneBy(['code' => $array[0] . 'PH' . $array[1]]);
                if (null === $phenixVariant) {
                    $phenixVariant = $this->productVariantRepository->findOneBy(['code' => $array[0] . 'PHEM' . $array[1]]);
                }
            }
            if (null === $phenixVariant) {
                $phenixVariant = $this->productVariantRepository->findOneBy(['code' => $code . 'PH']);
            }
            if (null !== $phenixVariant) {
                $newInsert = new ProductVariantEcotax();
                $newInsert->setEcotax($productVariantEcotax->getEcotax());
                $newInsert->setVariant($phenixVariant);
                $newInsert->setManufacturerPaid($productVariantEcotax->getManufacturerPaid());
                $this->productVariantEcotaxRepository->add($newInsert);
            }
        }

        // todo replace by COMMAND::SUCCESS on version 5.1
        return 0;
    }
}
