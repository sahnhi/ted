<?php

/********************************************************
 *
 * +-----------------------------------------+
 * |        TEMPORARY COMMAND FILE           |
 * +-----------------------------------------+
 * |  This file will be removed soon.       |
 * +-----------------------------------------+
 *
 ********************************************************/

declare(strict_types=1);

namespace App\Command\Importers;

use App\Doctrine\Entity\ProductCMSFaq;
use App\Doctrine\Entity\ProductCMSFocus;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * This is a TEMPORARY command to import the texts for products from a CSV file in order
 * to help the POs to add texts to products on the Sylius CMS. Once the new website has been
 * launched and the texts validated, this command should be removed.
 *
 * Thus, the commande is semantic but optimized, but there is no sense to spend more time
 * or worse try to optimize it because it will be removed later.
 */
class ProductV2ImporterCommand extends Command
{
    public function __construct(
        private readonly RepositoryInterface $productRepository,
        private readonly RepositoryInterface $productCmsTechnicalInfosRepository,
        private readonly RepositoryInterface $productCmsFaqQuestionRepository,
        private readonly RepositoryInterface $productCmsFocusDetailsRepository,
        private readonly FactoryInterface $productCmsFactory,
        private readonly FactoryInterface $productCmsTagsFactory,
        private readonly FactoryInterface $productCmsClaimsFactory,
        private readonly FactoryInterface $productCmsTechnicalInfosFactory,
        private readonly FactoryInterface $productCmsTechnicalDetailsFactory,
        private readonly FactoryInterface $productCmsFaqFactory,
        private readonly FactoryInterface $productCmsFaqQuestionFactory,
        private readonly FactoryInterface $productCmsFocusFactory,
        private readonly FactoryInterface $productCmsFocusDetailsFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        ->setName('app:import-products-v2')
        ->setDescription('Import products from v2')
        ->addArgument('filepath', InputArgument::REQUIRED, 'The file to import')
        ->setHelp('This command will import the text for products')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $products = $this->getTexts($input->getArgument('filepath'));
        $this->createProducts($products, $output);

        return 0;
    }

    private function getTexts(string $filepath): iterable
    {
        $slugger = new AsciiSlugger();
        $file = fopen($filepath, 'r');
        $data = [];
        while ($line = fgetcsv($file)) {
            $data[] = $line;
        }

        $texts = [];
        foreach ($data as $line => $infos) {
            foreach ($infos as $col => $value) {
                $texts[$col][$line] = $value;
            }
        }
        unset($data);

        $products = [];
        foreach ($texts as $key => $product) {
            if (0 === $key) {
                continue;
            }

            if (empty($product[4])) {
                continue;
            }
            $code = trim((string) $product[4]);
            $products[$code] = [];
            foreach ($product as $col => $text) {
                $baseName = $slugger->slug($texts[0][$col])->lower();
                $products[$code][(string) $baseName] = $text;
            }
        }

        return $products;
    }

    private function createProducts(array $data, OutputInterface $output): void
    {
        $i = 0;
        foreach ($data as $line) {
            $product = $this->productRepository->findOneBy(['code' => $line['code']]);
            if (!$product instanceof ProductInterface) {
                continue;
            }
            $this->insertCms($line, $product);
            $this->insertTags($line, $product);
            $this->insertClaims($line, $product);
            $this->insertTechnicalInfos($line, $product);
            $this->insertFaq($line, $product);
            $this->insertFocus($line, $product);

            $this->productRepository->add($product);

            $output->writeln(sprintf('%s - Texts for product %s imported', ++$i, $product->getCode()));
        }
        $output->writeln(sprintf('Total of products imported: %s', $i));
    }

    private function insertCms(array $line, Product $product): void
    {
        $productCms = $this->productCmsFactory->createNew();
        $productCms->setCurrentLocale('fr_FR');
        $productCms->setSubInfo($line['claim-1-atout-n01']);
        $productCms->setSubInfoBis($line['claim-2-technique']);
        $productCms->setSubTitle($line['sous-titre-produit']);
        $product->setProductCMS($productCms);
    }

    private function insertTags(array $line, Product $product): void
    {
        $productCmsTags = $this->productCmsTagsFactory->createNew();
        $productCmsTags->setCurrentLocale('fr_FR');
        $productCmsTags->setPrimaryTag($line['tag']);
        $product->setProductCMSTags($productCmsTags);
    }

    private function insertClaims(array $line, Product $product): void
    {
        $clear = true;
        for ($i = 1; $i <= 5; ++$i) {
            if (empty($line[$i])) {
                continue;
            }
            if ($clear) {
                $product->clearClaims();
                $clear = false;
            }
            $claim = $this->productCmsClaimsFactory->createNew();
            $claim->setCurrentLocale('fr_FR');
            $claim->setClaim($line[$i]);
            $product->addClaim($claim);
        }
    }

    private function insertTechnicalInfos(array $line, Product $product): void
    {
        foreach ($product->getProductCMSTechnicalInfos() as $productCmsTechnicalInfos) {
            $product->removeProductCMSTechnicalInfo($productCmsTechnicalInfos);
            $this->productCmsTechnicalInfosRepository->remove($productCmsTechnicalInfos);
        }

        // Section 1
        $productCmsTechnicalInfos = $this->productCmsTechnicalInfosFactory->createNew();
        $productCmsTechnicalInfos->setCurrentLocale('fr_FR');
        $productCmsTechnicalInfos->setTitle($line['bloc-fiche-technique-1-title']);

        $productCmsTechnicalDetails = $this->productCmsTechnicalDetailsFactory->createNew();
        $productCmsTechnicalDetails->setCurrentLocale('fr_FR');
        $productCmsTechnicalDetails->setHeader($line['bloc-fiche-technique-1-category-1-title']);
        $productCmsTechnicalDetails->setText($line['bloc-fiche-technique-1-category-1-text']);
        $productCmsTechnicalInfos->addProductCMSTechnicalDetail($productCmsTechnicalDetails);

        for ($i = 2; $i <= 3; ++$i) {
            $productCmsTechnicalDetails = $this->productCmsTechnicalDetailsFactory->createNew();
            $productCmsTechnicalDetails->setCurrentLocale('fr_FR');
            $productCmsTechnicalDetails->setHeader($line['title-item-' . $i]);
            $productCmsTechnicalDetails->setText($line['item-' . $i]);
            $productCmsTechnicalInfos->addProductCMSTechnicalDetail($productCmsTechnicalDetails);
        }

        // insert to product
        $product->addProductCMSTechnicalInfo($productCmsTechnicalInfos);
        unset($productCmsTechnicalInfos);

        // Section 2
        $productCmsTechnicalInfos = $this->productCmsTechnicalInfosFactory->createNew();
        $productCmsTechnicalInfos->setCurrentLocale('fr_FR');
        $productCmsTechnicalInfos->setTitle($line['titre-2']);

        for ($i = 4; $i <= 6; ++$i) {
            $productCmsTechnicalDetails = $this->productCmsTechnicalDetailsFactory->createNew();
            $productCmsTechnicalDetails->setCurrentLocale('fr_FR');
            $productCmsTechnicalDetails->setHeader($line['title-item-' . $i]);
            $productCmsTechnicalDetails->setText($line['item-' . $i]);
            $productCmsTechnicalInfos->addProductCMSTechnicalDetail($productCmsTechnicalDetails);
        }

        $product->addProductCMSTechnicalInfo($productCmsTechnicalInfos);
    }

    private function insertFaq(array $line, Product $product): void
    {
        foreach ($product->getProductCMSFaq()->getQuestions() as $productCmsFaqQuestion) {
            $product->getProductCMSFaq()->removeQuestion($productCmsFaqQuestion);
            $this->productCmsFaqQuestionRepository->remove($productCmsFaqQuestion);
        }

        $productCmsFaq = $product->getProductCMSFaq();
        $productCmsFaq->setTitle($line['titre-faq']);
        if (!$productCmsFaq instanceof ProductCMSFaq) {
            $productCmsFaq = $this->productCmsFaqFactory->createNew();
        }
        for ($i = 1; $i <= 10; ++$i) {
            if (!isset($line['question-' . $i]) || empty($line['question-' . $i])) {
                continue;
            }
            $question = $line['question-' . $i];
            $answer = $line['reponse-' . $i];

            $productCmsFaqQuestion = $this->productCmsFaqQuestionFactory->createNew();
            $productCmsFaqQuestion->setCurrentLocale('fr_FR');
            $productCmsFaqQuestion->setQuestion($question);
            $productCmsFaqQuestion->setAnswer($answer);
            $productCmsFaq->addQuestion($productCmsFaqQuestion);
        }
        $product->setProductCMSFaq($productCmsFaq);
    }

    private function insertFocus(array $line, Product $product): void
    {
        if ($product->getProductCMSFocus() instanceof ProductCMSFocus) {
            foreach ($product->getProductCMSFocus()->getProductCMSFocusDetails() as $productCmsFocusDetails) {
                $product->getProductCMSFocus()->removeProductCMSFocusDetail($productCmsFocusDetails);
                $this->productCmsFocusDetailsRepository->remove($productCmsFocusDetails);
            }
        }

        $productCmsFocusDetails = $this->productCmsFocusDetailsFactory->createNew();
        $productCmsFocusDetails->setCurrentLocale('fr_FR');
        $productCmsFocusDetails->setTerm($line['titre-focus']);
        $productCmsFocusDetails->setDescription($line['description-focus']);

        $productCmsFocus = $product->getProductCMSFocus();
        if (!$productCmsFocus instanceof ProductCMSFocus) {
            $productCmsFocus = $this->productCmsFocusFactory->createNew();
        }
        $productCmsFocus->addProductCMSFocusDetail($productCmsFocusDetails);

        $product->setProductCMSFocus($productCmsFocus);
    }
}
