<?php

namespace App\Services\Cron\Review;

use App\Doctrine\Entity\AdminParameter;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\AdminParameterRepository;
use App\Doctrine\Repository\AskReviewTemplateRepositoryInterface;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Message\Mail;
use App\Services\Mail\MailerEmailInterface;
use App\Services\Review\Seek\Seeker\NotFoundException;
use App\Services\Review\Seek\SeekerFactory;
use App\Utils\DateRange;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Messenger\MessageBusInterface;

class Seek
{
    private readonly DateRange $dateRange;

    /**
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository,
        private readonly AskReviewTemplateRepositoryInterface $askReviewTemplateRepository,
        private readonly AdminParameterRepository $adminParameterRepository,
        private readonly MessageBusInterface $bus,
        private readonly SeekerFactory $seekerFactory
    ) {
        $interval = $this->adminParameterRepository
            ->getByKey(AdminParameter::DAYS_DELAY_REVIEW_KEY);
        $interval = null !== $interval ? $interval->getValue() : '14';

        $min14DayDate = new \DateTime();
        $interval14Days = new \DateInterval('P' . $interval . 'D');
        $min14DayDate->sub($interval14Days);

        $max14DayDate = clone $min14DayDate;
        $interval24h = new \DateInterval('PT24H');
        $max14DayDate->add($interval24h);

        $this->dateRange = new DateRange(
            \DateTimeImmutable::createFromMutable($min14DayDate),
            \DateTimeImmutable::createFromMutable($max14DayDate)
        );
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(): void
    {
        $totalOrders = $this->orderRepository->countReadyForReviewSeekers($this->dateRange);

        foreach ($this->askReviewTemplateRepository->findBy([], ['order' => 'ASC']) as $reviewTemplate) {
            try {
                $seeker = $this->seekerFactory->get($reviewTemplate);
                $seeker->seekReviews($this->dateRange, $totalOrders);
            } catch (NotFoundException) {
            }
        }

        $this->sendByMail();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function sendByMail(): void
    {
        $orders = $this->orderRepository->findAllForMailReviews($this->dateRange);

        foreach ($orders as $order) {
            /* @var Order $order */
            $this->bus->dispatch(new Mail(MailerEmailInterface::TYPE_REVIEW_EMAIL, $order, [
                'recipients' => [
                    [
                        'email' => $order->getCustomer()->getEmail(),
                        'firstname' => $order->getCustomer()->getFirstName(),
                        'lastname' => $order->getCustomer()->getLastName(),
                    ],
                ],
            ]));

            $order->setReviewType(Order::REVIEW_TYPE_MANDRILL)
                ->setReviewEmailSent(true);
            $this->orderRepository->add($order);
        }

        $this->entityManager->flush();
    }
}
