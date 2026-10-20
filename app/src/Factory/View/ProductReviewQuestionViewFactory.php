<?php

namespace App\Factory\View;

use App\Doctrine\Entity\ProductReviewQuestion;
use App\View\ProductReviewQuestionView;
use Sylius\Component\Core\Model\ChannelInterface;

class ProductReviewQuestionViewFactory implements ProductReviewQuestionViewFactoryInterface
{
    public function create(ProductReviewQuestion $question, ChannelInterface $channel, string $localeCode): ProductReviewQuestionView
    {
        $view = new ProductReviewQuestionView();
        $view->code = $question->getQuestion();
        $view->name = $question->getName($localeCode);
        $view->questionType = $question->getQuestionType();
        $view->position = $question->getPosition();

        foreach ($question->getProductReviewAnswerChoices() as $answer) {
            $view->answers[] = [
                'id' => $answer->getId(),
                'code' => $answer->getCode(),
                'answer' => $answer->getTranslation($localeCode)->getName(),
            ];
        }

        return $view;
    }
}
