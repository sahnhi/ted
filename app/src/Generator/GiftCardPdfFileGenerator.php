<?php

namespace App\Generator;

use App\Model\GiftCard\GiftCardPdf;
use Knp\Snappy\Pdf;
use Setono\SyliusGiftCardPlugin\Model\GiftCardConfigurationInterface;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Setono\SyliusGiftCardPlugin\Provider\GiftCardChannelConfigurationProviderInterface;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Twig\Environment;

final class GiftCardPdfFileGenerator implements GiftCardPdfFileGeneratorInterface
{
    private const FILE_EXTENSION = '.pdf';

    public function __construct(
        private readonly GiftCardRepositoryInterface $giftCardRepository,
        private readonly GiftCardChannelConfigurationProviderInterface $configurationProvider,
        private readonly Pdf $pdfGenerator,
        private readonly Environment $templateEngine,
        private readonly string $template
    ) {
    }

    public function generate(string $giftCardId): GiftCardPdf
    {
        /** @var GiftCardInterface $giftCard */
        $giftCard = $this->giftCardRepository->find($giftCardId);

        $filename = 'gift_card' . self::FILE_EXTENSION;

        $configuration = $this->configurationProvider->getConfigurationForGiftCard($giftCard);
        if (!$configuration instanceof GiftCardConfigurationInterface) {
            throw new \Exception('Configuration not found for this gift card. Create one by going to the gift card configuration.');
        }

        $pdf = $this->pdfGenerator->getOutputFromHtml(
            $this->templateEngine->render($this->template, [
                'giftCard' => $giftCard,
                'configuration' => $configuration,
            ]),
            [
                'orientation' => 'landscape',
            ]
        );

        return new GiftCardPdf($filename, $pdf);
    }
}
