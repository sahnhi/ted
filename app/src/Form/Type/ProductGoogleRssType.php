<?php

namespace App\Form\Type;

use App\Doctrine\Entity\ProductGoogleRss;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductGoogleRssType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku')
            ->add('gtin')
            ->add('mpn')
            ->add('urlReview')
            ->add('productUrl')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductGoogleRss::class,
        ]);
    }
}
