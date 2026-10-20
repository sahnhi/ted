<?php

namespace App\Command;

use App\Doctrine\Entity\ProductReviewAnswer;
use App\Doctrine\Repository\ProductReviewAnswerChoicesRepository;
use App\Doctrine\Repository\ProductReviewAnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SplitProductReviewAnswerCommand extends Command
{
    protected static $defaultName = 'app:split-review-answer';

    public function __construct(
        private readonly ProductReviewAnswerRepository $productReviewAnswerRepository,
        private readonly ProductReviewAnswerChoicesRepository $productReviewAnswerChoicesRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Split answer for product_review_answer with multi answer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productReviewAnswer = $this->productReviewAnswerRepository->getReviewWithMultiAnswer();

        foreach ($productReviewAnswer as &$review) {
            $answersText = explode(',', (string) $review->getAnswer());
            foreach ($answersText as $key => $row) {
                $name = trim($row);
                if ('trop chaud / froid la nuit' === $name) {
                    $name = 'Trop chaud / Trop froid';
                }
                $answerChoice = $this->productReviewAnswerChoicesRepository->getAnswerChoices($name);
                if (null !== $answerChoice) {
                    if (0 === $key) {
                        $review->setAnswer($answerChoice->getCode());
                        $review->setAnswerChoices($answerChoice);

                        $this->entityManager->persist($review);
                    } else {
                        $newInsert = new ProductReviewAnswer();
                        $newInsert->setQuestion($review->getQuestion());
                        $newInsert->setReview($review->getReview());
                        $newInsert->setAnswer($answerChoice->getCode());
                        $newInsert->setAnswerChoices($answerChoice);

                        $this->productReviewAnswerRepository->add($newInsert);
                    }
                }
            }
        }

        // todo replace by COMMAND::SUCCESS on version 5.1
        return 0;
    }
}
