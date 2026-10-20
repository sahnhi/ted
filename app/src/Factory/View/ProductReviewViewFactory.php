<?php

namespace App\Factory\View;

use App\Doctrine\Entity\ProductReviewAnswer;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductReview;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\ProductGoogleRssRepositoryInterface;
use App\View\ProductIdView;
use App\View\ProductReviewAnswerView;
use App\View\ProductReviewView;
use App\View\ProductView;
use App\View\RatingsView;
use App\View\ReviewUrlView;
use Sylius\Component\Review\Model\ReviewInterface;

class ProductReviewViewFactory implements ProductReviewViewFactoryInterface
{
    public const FRONT_URL = 'https://www.tediber.com';

    public function __construct(
        private readonly ProductGoogleRssRepositoryInterface $productGoogleRssRepository
    ) {
    }

    public function create(ProductReview $productReview): ProductReviewView
    {
        $view = new ProductReviewView();

        $updatedAt = $productReview->getUpdatedAt();

        $view->reviewId = $productReview->getId();
        $view->firstName = $productReview->getAuthor()->getFirstName();
        $view->comment = $productReview->getComment();
        $view->rating = $productReview->getRating();
        $view->title = $productReview->getTitle();
        $view->savAnswer = $productReview->getSavAnswer();
        $view->review_timestamp = $view->date = $updatedAt instanceof \DateTimeInterface ? $updatedAt->format(DATE_ATOM) : $productReview->getCreatedAt()->format(DATE_ATOM);
        $view->verified = ReviewInterface::STATUS_ACCEPTED === $productReview->getStatus();
        $view->translated = null !== $productReview->getParent();
        $view->reviewer = ['name' => $view->firstName];

        /**
         * @var Product $product
         */
        $product = $productReview->getReviewSubject();
        $productGoogleRss = $product->getProductGoogleRss();

        $url = new ReviewUrlView();
        $reviewUrl = $product->getSlug();

        if (null !== $productGoogleRss) {
            $reviewUrl = $productGoogleRss->getUrlReview() ?? $product->getSlug();

            $ratingView = new RatingsView();
            $ratingView->overall = $view->rating;
            $view->ratings[] = $ratingView;

            $productView = new ProductView();
            $productView->url = self::FRONT_URL . '/' . $product->getSlug();
            $view->products[] = $productView;

            $productIdView = new ProductIdView();
            $productIdView->gtins[] = $productGoogleRss->getGtin();
            $productIdView->mpns[] = $productGoogleRss->getMpn();
            $productIdView->skus[] = $productGoogleRss->getSku();
            $productView->ids[] = $productIdView;
        }

        $variantsRss = $this->productGoogleRssRepository->getForProductVariants($product);

        /* @var ProductVariantInterface $variant */
        foreach ($variantsRss as $variantRss) {
            $productView = new ProductView();
            $productView->url = self::FRONT_URL . '/' . $variantRss->getProductUrl();

            $productIdView = new ProductIdView();
            $productIdView->gtins[] = $variantRss->getGtin();
            $productIdView->mpns[] = $variantRss->getMpn();
            $productIdView->skus[] = $variantRss->getSku();

            $productView->ids[] = $productIdView;
            $view->products[] = $productView;
        }

        $url->reviewUrl = self::FRONT_URL . '/avis/' . $reviewUrl;
        $view->url[] = $url;

        /**
         * @var ProductReviewAnswer $answer
         */
        foreach ($productReview->getAnswers() as $answer) {
            if (false === $answer->getQuestion()->isEnabled()) {
                continue;
            }

            $answerView = new ProductReviewAnswerView();
            $answerView->question = $answer->getQuestion()->getQuestion();
            $answerView->answerCode = $answer->getAnswer();
            $answerView->answer = (null !== $answer->getAnswerChoices()) ? $answer->getAnswerChoices()->getTranslation($productReview->getLocale())->getName() : '';
            $view->answers[] = $answerView;
        }

        if (null !== $productReview->getOrder()) {
            $order = $productReview->getOrder();
            $view->orderId = $order->getNumber();
            $view->orderDate = $order->getCreatedAt()->format(DATE_ATOM);

            if (null !== $order->getShippingAddress()) {
                $view->city = $order->getShippingAddress()->getCity();
            }
        }

        if (null !== $productReview->getAuthor() && null !== ($birthday = $productReview->getAuthor()->getBirthday())) {
            $now = new \DateTime();
            $view->age = $birthday->diff($now)->y;
        }

        return $view;
    }
}
