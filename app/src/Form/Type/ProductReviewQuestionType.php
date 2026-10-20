<?php

namespace App\Form\Type;

use App\Doctrine\Entity\ProductReviewAnswerChoices;
use App\Doctrine\Entity\ProductReviewQuestion;
use App\Doctrine\Entity\Sylius\Product;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductReviewQuestionType extends AbstractResourceType
{
    public const INPUT_TYPE = [
        'checkbox' => 'checkbox',
        'radio' => 'radio',
        'rating' => 'rating',
        'text' => 'text',
    ];

    public function __construct()
    {
        parent::__construct(ProductReviewQuestion::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $answers = [];
        foreach ($builder->getData()->getProductReviewAnswerChoices() as $productReviewAnswerChoices) {
            $answers[] = $productReviewAnswerChoices;
        }
        $builder
            ->add('question', TextType::class, [
                'label' => 'sylius.form.product_review_question.question',
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'sylius.form.product_review_question.enabled',
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'sylius.form.product_review_question.product',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ProductReviewQuestionTranslationType::class,
                'label' => 'sylius.form.product_review_question.translations',
            ])
            ->add('questionType', ChoiceType::class, [
                'label' => 'sylius.form.product_review_question.questionType',
                'choices' => self::INPUT_TYPE,
            ])
            ->add('position', NumberType::class, [
                'label' => 'sylius.form.product_review_question.position',
                'attr' => [
                    'style' => 'display:inline-flex',
                ],
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($answers): void {
                $event->getForm()->get('productReviewAnswerChoices')->setData($answers);
            })
            ->add('productReviewAnswerChoices', EntityType::class, [
                'class' => ProductReviewAnswerChoices::class,
                'label' => 'sylius.form.product_review_question.answer',
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('p'),
                'attr' => [
                    'class' => 'select2',
                ],
                'multiple' => true,
                'required' => false,
                'mapped' => false,
                'choice_label' => fn ($entity): string => $entity->getName() . ' (' . $entity->getCode() . ')',
                'by_reference' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
                $questionData = $event->getData();
                $productReviewAnswerChoices = $event->getForm()->get('productReviewAnswerChoices')->getData();

                foreach ($questionData->getProductReviewAnswerChoices() as $answer) {
                    if (!in_array($answer, $productReviewAnswerChoices->toArray())) {
                        $questionData->removeProductReviewAnswerChoice($answer);
                    }
                }

                if (!empty($productReviewAnswerChoices)) {
                    foreach ($productReviewAnswerChoices as $answer) {
                        $questionData->addProductReviewAnswerChoice($answer);
                    }
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductReviewQuestion::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'sylius_product_review_question';
    }
}
