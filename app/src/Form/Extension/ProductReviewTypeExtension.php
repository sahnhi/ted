<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductReview;
use App\Form\Type\LocaleType;
use App\Model\Role;
use Sylius\Bundle\CoreBundle\Form\Type\Product\ProductReviewType;
use Sylius\Component\Review\Model\Reviewer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ProductReviewTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->authorizationChecker->isGranted(Role::ROLE_ADMIN)) {
            $builder
                ->add('product', EntityType::class, [
                    'class' => Product::class,
                ]);
        }

        $builder
            ->add('locale', LocaleType::class)
            ->add('status', ChoiceType::class, [
                'choices' => ['sylius.ui.new' => ProductReview::STATUS_NEW, 'sylius.ui.accepted' => ProductReview::STATUS_ACCEPTED, 'sylius.ui.rejected' => ProductReview::STATUS_REJECTED],
            ])
            ->add('star', CheckboxType::class, [
                'label' => 'sylius.ui.product_review.star',
            ])
            ->add('savAnswer', TextareaType::class, [
                'label' => 'sylius.ui.product_review.savAnswer',
                'required' => false,
            ])
        ;

        if (isset($options['product_review']) && $options['product_review']) {
            $builder
                ->add('author', EntityType::class, [
                    'class' => Reviewer::class,
                    'choice_label' => 'firstName',
                ])
                ->add('product', EntityType::class, [
                    'class' => Product::class,
                ])
            ;
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductReviewType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['product_review' => null, 'data_class' => ProductReview::class]);
    }
}
