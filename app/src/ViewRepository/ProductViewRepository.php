<?php

namespace App\ViewRepository;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use App\Factory\View\ProductReviewQuestionViewFactoryInterface;
use App\Factory\View\ProductViewFactoryInterface;
use App\View\ProductListReviewQuestionListView;
use App\View\ProductListView;
use App\View\ProductReviewQuestionListView;
use App\View\ProductView;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Webmozart\Assert\Assert;

class ProductViewRepository implements ProductViewRepositoryInterface
{
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductViewFactoryInterface $productViewFactory,
        private readonly ProductReviewQuestionViewFactoryInterface $questionViewFactory
    ) {
    }

    public function findAllByChannel(string $channelCode, bool $bounds = false): ProductListView
    {
        $channel = $this->getChannel($channelCode);
        $localeCode = $this->getLocaleCode($channel);

        $products = $this->productRepository->findAllByChannelAndLocale($channel, $localeCode);
        Assert::notNull($products, sprintf('Products not found in %s locale.', $localeCode));

        $view = new ProductListView();
        foreach ($products as $product) {
            $view->items[] = $this->productViewFactory->listShopApi($product, $channel, $localeCode, $bounds);
        }

        return $view;
    }

    public function findOneByCode(string $channelCode, string $code, string $localeCode, bool $withVariants = false): ProductView
    {
        $channel = $this->getChannel($channelCode);
        $this->assertLocaleSupport($localeCode, $channel->getLocales());

        $product = $this->productRepository->findOneByChannelAndCode($channel, $code);
        Assert::notNull($product, sprintf('Product with code %s not found in %s locale.', $code, $localeCode));

        /* @var ProductInterface $product */

        return $this->productViewFactory->create($product, $channel, $localeCode, withVariants: $withVariants);
    }

    private function getChannel(string $channelCode): ChannelInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->findOneByCode($channelCode);
        Assert::notNull($channel, sprintf('Channel with code %s has not been found.', $channelCode));

        return $channel;
    }

    private function getLocaleCode(ChannelInterface $channel): string
    {
        $localeCode = $channel->getDefaultLocale()->getCode();
        $this->assertLocaleSupport($localeCode, $channel->getLocales());

        return $localeCode;
    }

    /**
     * @param iterable|LocaleInterface[] $supportedLocales
     */
    private function assertLocaleSupport(string $localeCode, iterable $supportedLocales): void
    {
        $supportedLocaleCodes = [];
        foreach ($supportedLocales as $locale) {
            $supportedLocaleCodes[] = $locale->getCode();
        }

        Assert::oneOf($localeCode, $supportedLocaleCodes);
    }

    public function findAllWithReviewRequested(string $channel, string $locale, array $productCodes, array $questionsAnswered): ProductListReviewQuestionListView
    {
        $products = $this->productRepository->findAllWithReviewRequested(
            $locale,
            $productCodes,
            $questionsAnswered
        );
        $channel = $this->getChannel($channel);
        $questionsList = [];
        $view = new ProductListReviewQuestionListView();
        foreach ($products as $product) {
            $productView = $this->createProductView($product, $channel, $locale);
            $productKey = $productView->product->getReviewTaxon() ?? $productView->product->code;

            if (($key = array_search($productView->product->code, $productCodes)) !== false) {
                unset($productCodes[$key]);
                $productCodes[] = $productKey;
            }

            if (false === array_key_exists($productKey, $view->products)) {
                $view->products[$productKey] = $productView;
            }

            if (null !== $product->getProductReviewQuestions()) {
                foreach ($product->getProductReviewQuestions() as $question) {
                    if (empty($productCodes) && isset($questionsList[$productKey]) && in_array($question->getQuestion(), $questionsList[$productKey])) {
                        continue;
                    }
                    $questionsList[$productKey][] = $question->getQuestion();
                    $view->products[$productKey]->questions[] = $this->questionViewFactory->create($question, $channel, $locale);
                }
            }
        }

        return $view;
    }

    public function createProductView($product, $channel, $localeCode): ProductReviewQuestionListView
    {
        $productView = new ProductReviewQuestionListView();
        $productView->product = $this->productViewFactory->create($product, $channel, $localeCode, true);

        return $productView;
    }
}
