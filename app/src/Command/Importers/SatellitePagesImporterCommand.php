<?php

declare(strict_types=1);

namespace App\Command\Importers;

use App\Doctrine\Entity\SatellitePages\SatellitePage;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SatellitePagesImporterCommand extends Command
{
    public function __construct(
        private readonly FactoryInterface $satellitePageFactory,
        private readonly FactoryInterface $satellitePageClaimFactory,
        private readonly FactoryInterface $satellitePageTechnicalInfoFactory,
        private readonly FactoryInterface $satellitePageTechnicalDetailsFactory,
        private readonly RepositoryInterface $satellitePageRepository,
        private readonly RepositoryInterface $satellitePageTranslationRepository,
        private readonly RepositoryInterface $productVariantRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:import-satellite-pages')
            ->setDescription('Import satellite pages')
            ->addArgument('filepath', InputArgument::REQUIRED, 'The file to import')
            ->setHelp('This command will import the satellite pages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filepath = $input->getArgument('filepath');
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

        $variants = [];
        foreach ($texts as $key => $product) {
            if (0 === $key) {
                continue;
            }

            $variants[$key] = [];
            foreach ($product as $col => $text) {
                $baseName = $slugger->slug($texts[0][$col])->lower();
                $variants[$key][(string) $baseName] = $text;
            }
        }

        foreach ($variants as $variant) {
            $this->importData($variant);
        }

        return 0;
    }

    private function importData(array $data): void
    {
        if (empty($data['code-variant'])) {
            return;
        }
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $data['code-variant']]);
        if (null === $productVariant) {
            return;
        }

        $satellitePage = $this->getSatellitePage($this->generateUrl($data['url']));
        $satellitePage->setCurrentLocale('fr_FR');
        $satellitePage->setProductVariant($productVariant);
        $satellitePage->setSatelliteUrl($this->generateUrl($data['url']));
        $satellitePage->setFocus($data['concu-pour-vous']);
        $satellitePage->setTag($this->parseTag($data['tag']));
        $satellitePage->setMainPhrase($data['phrase-marque-85-caracteres-max']);
        $satellitePage->setQualityClaim($data['claim-1-atout-n01']);
        $satellitePage->setTechnicalClaim($data['claim-2-technique']);
        $satellitePage->setName($data['nom']);

        $this->addClaims($satellitePage, $data);
        $this->addFeatures($satellitePage, $data);

        $this->satellitePageRepository->add($satellitePage);
    }

    private function getSatellitePage(string $url): SatellitePage
    {
        $translation = $this->satellitePageTranslationRepository->findOneBy(['satelliteUrl' => urlencode($this->generateUrl($url))]);

        return $translation?->getTranslatable() ?? $this->satellitePageFactory->createNew();
    }

    private function generateUrl(string $url): string
    {
        return str_replace('https://www.tediber.com/', '', $url);
    }

    private function parseTag(string $tag): string
    {
        if ('/' === $tag) {
            $tag = '';
        }

        return $tag;
    }

    private function addClaims(SatellitePage $satellitePage, array $data): void
    {
        $satellitePage->clearClaims();
        for ($i = 1; $i <= 5; ++$i) {
            if (empty($data[$i])) {
                continue;
            }
            $claim = $this->satellitePageClaimFactory->createNew();
            $claim->setCurrentLocale('fr_FR');
            $claim->setSatellitePage($satellitePage);
            $claim->setClaim($data[$i]);
            $satellitePage->addClaim($claim);
        }
    }

    private function addFeatures(SatellitePage $satellitePage, array $data): void
    {
        $features = [
            'Détail produit' => [
                'composition' => 'Composition',
                'dimension' => 'Dimension',
                'entretien' => 'Entretien',
            ],
            'Livraison et retour' => [
                'factory' => 'Fabrication',
                'livraison' => 'Livraison',
                'montage' => 'Montage',
                'reprise' => 'Reprise de l\'ancienne literie',
                '100nuits' => '100 nuits d\'essai',
            ],
        ];

        $satellitePage->clearTechnicalInfos();
        foreach ($features as $feature => $items) {
            $technicalInfo = null;
            foreach ($items as $item => $label) {
                if (isset($data[$item])) {
                    if (is_null($technicalInfo)) {
                        $technicalInfo = $this->satellitePageTechnicalInfoFactory->createNew();
                        $technicalInfo->setCurrentLocale('fr_FR');
                        $technicalInfo->setSatellitePage($satellitePage);
                        $technicalInfo->setTitle($feature);
                    }

                    $productDetail = $this->satellitePageTechnicalDetailsFactory->createNew();
                    $productDetail->setCurrentLocale('fr_FR');
                    $productDetail->setTitle($label);
                    $productDetail->setDescription($data[$item]);
                    $productDetail->setTechnicalInfo($technicalInfo);
                    $technicalInfo->addTechnicalDetail($productDetail);
                }
            }
            if (!is_null($technicalInfo)) {
                $satellitePage->addTechnicalInfo($technicalInfo);
            }
        }
    }
}
