<?php

namespace App\Api;

use App\Api\TrustPilot\DTO\Invitation;
use App\Api\TrustPilot\DTO\Review;
use App\Doctrine\Entity\AskReviewTemplate;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ProductReview;
use App\OAuth2\Provider;
use App\Services\Sentry\ReportingInterface;
use App\Utils\StringUtils;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Sentry;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Review\Model\ReviewInterface;

class TrustPilot
{
    public const API_AUTHORIZE_URL = 'https://authenticate.trustpilot.com';
    public const API_ACCESS_TOKEN_URL = 'https://api.trustpilot.com/v1/oauth/oauth-business-users-for-applications/accesstoken';
    public const API_INVITATION_URL = 'https://invitations-api.trustpilot.com/v1/private/business-units/{businessUnitId}/email-invitations';
    public const API_REVIEWS_URL = 'https://api.trustpilot.com/v1/private/business-units/{businessUnitId}/reviews';
    private AskReviewTemplate $askReviewTemplate;

    private Client $client;
    private Serializer $serializer;
    private ?Provider $provider = null;

    private string $seekReviewUri;

    public function getAskReviewTemplate(): AskReviewTemplate
    {
        return $this->askReviewTemplate;
    }

    public function setAskReviewTemplate(AskReviewTemplate $askReviewTemplate): TrustPilot
    {
        $this->askReviewTemplate = $askReviewTemplate;

        return $this;
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly string $login,
        private readonly string $password,
        private readonly string $businessUnitId,
        private readonly ReportingInterface $reporting
    ) {
        $this->client = new Client();
        $this->serializer = SerializerBuilder::create()
            ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
            ->build();

        $this->seekReviewUri = str_replace('{businessUnitId}', $this->businessUnitId, self::API_INVITATION_URL);
    }

    /**
     * Used for calls requiring OAuth authentication.
     */
    private function init(): void
    {
        if (is_null($this->provider)) {
            $this->provider = new Provider(
                $this->apiKey,
                $this->apiSecret,
                self::API_AUTHORIZE_URL,
                self::API_ACCESS_TOKEN_URL
            );
            $this->provider->connect($this->login, $this->password);
        }
    }

    public function sendOrder(Order $order): void
    {
        $this->init();

        $invitation = new Invitation($order, $this->askReviewTemplate->getTemplateId());
        $payload = $this->serializer->toArray($invitation);

        try {
            $this->client
                ->post(
                    $this->seekReviewUri,
                    [
                        'json' => $payload,
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->provider->getToken()->getToken(),
                            'Accept' => 'application/json',
                        ],
                    ]
                );

            $order->setReviewType(Order::REVIEW_TYPE_TRUST_PILOT)
                ->setReviewEmailSent(true);
            $this->entityManager->persist($order);
        } catch (\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['TrustPilot_url' => $this->seekReviewUri],
                ['payload' => $payload]
            );
        }
    }

    public function retrieveReviews(ProductInterface $product, ?ProductReview $lastReview = null): void
    {
        $this->init();
        $uri = str_replace('{businessUnitId}', $this->businessUnitId, self::API_REVIEWS_URL)
            . '?perPage=100'
            . '&orderBy=createdat.asc';

        $uri .= null !== $lastReview ?
            '&startDateTime=' . $lastReview->getCreatedAt()->add(new \DateInterval('PT1S'))->format('Y-m-d\TH:i:s') :
            '';

        $i = 1;
        try {
            while (null !== $uri) {
                $response = $this->client
                    ->get(
                        $uri,
                        [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $this->provider->getToken()->getToken(),
                                'Accept' => 'application/json',
                            ],
                        ]
                    );

                $payload = $response->getBody()->getContents();
                /** @var Review $review */
                $review = $this->serializer->deserialize(
                    $payload,
                    Review::class,
                    'json'
                );

                $this->createReviews($product, $review->getReviews());

                $nextPageLink = array_filter($review->getLinks(), fn (Review\Link $link): bool => Review\Link::REL_NEXT_PAGE === $link->getRel());

                $uri = !empty($nextPageLink) ? current($nextPageLink)->getHref() : null;
            }
        } catch (\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                ['TrustPilot_url' => $uri],
                ['Error message' => 'Cannot retrieve reviews']
            );
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function createReviews(ProductInterface $product, array $reviews): void
    {
        foreach ($reviews as $review) {
            if (null === $review->getReferralEmail()) {
                continue;
            }
            $author = $this->customerRepository->findByEmailPart($review->getReferralEmail());
            if (empty($author)) {
                continue;
            }
            $author = $author[0];

            $productReview = new ProductReview();

            $productReview->setAuthor($author);
            $productReview->setProduct($product);
            $productReview->setReviewSubject($product);
            $productReview->setCreatedAt($review->getCreatedAt());
            $productReview->setRating($review->getStars());
            $productReview->setStar(true);
            $productReview->setTitle($review->getTitle());
            $productReview->setComment(StringUtils::removeEmoji($review->getText()));

            if ($review->getStars() >= 4) {
                $productReview->setStatus(ReviewInterface::STATUS_ACCEPTED);
            }

            $this->entityManager->persist($productReview);
        }
    }
}
