<?php

namespace App\Form\Type;

use App\Doctrine\Entity\ProductReviewAnswerChoicesTranslation;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductReviewAnswerChoicesTranslationType extends AbstractResourceType
{
    public function __construct()
    {
        parent::__construct(ProductReviewAnswerChoicesTranslation::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'sylius.form.product_review_answer_choices.name',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'sylius_product_review_translation_translation';
    }
}
