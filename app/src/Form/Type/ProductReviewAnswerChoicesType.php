<?php

namespace App\Form\Type;

use App\Doctrine\Entity\ProductReviewAnswerChoices;
use App\Doctrine\Entity\ProductReviewQuestion;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductReviewAnswerChoicesType extends AbstractResourceType
{
    public const INPUT_TYPE = [
        'checkbox' => 'checkbox',
        'radio' => 'radio',
        'rating' => 'rating',
        'text' => 'text',
    ];

    public function __construct()
    {
        parent::__construct(ProductReviewAnswerChoices::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $questions = [];
        foreach ($builder->getData()->getQuestions() as $question) {
            $questions[] = $question;
        }
        $builder
            ->add('code', TextType::class, [
                'label' => 'sylius.form.product_review_answer_choices.code',
            ])
            ->add('questions', EntityType::class, [
                'class' => ProductReviewQuestion::class,
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('p'),
                'attr' => [
                    'class' => 'select2',
                ],
                'multiple' => true,
                'required' => false,
                'mapped' => false,
                'choice_label' => fn ($entity) => $entity->getName(),
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ProductReviewAnswerChoicesTranslationType::class,
                'label' => 'sylius.form.product_review_answer_choices.translations',
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($questions): void {
                $event->getForm()->get('questions')->setData($questions);
            })
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
                $answerData = $event->getData();
                $productReviewQuestion = $event->getForm()->get('questions')->getData();

                foreach ($answerData->getQuestions() as $question) {
                    $answerData->removeQuestion($question);
                }

                if (!empty($productReviewQuestion)) {
                    foreach ($productReviewQuestion as $question) {
                        $answerData->addQuestion($question);
                    }
                }
            })
        ;
    }

    public function getBlockPrefix()
    {
        return 'sylius_product_review_answer_choices';
    }
}
