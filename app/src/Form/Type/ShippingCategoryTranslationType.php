<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Doctrine\Entity\ShippingCategoryTranslation;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ShippingCategoryTranslationType extends AbstractResourceType
{
    public function __construct(array $validationGroups = [])
    {
        parent::__construct(ShippingCategoryTranslation::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('claims', TextareaType::class, [
                'required' => false,
                'label' => 'app.ui.shipping_category.claims',
                'help' => 'app.ui.shipping_method_category.claims_help',
                'help_html' => true,
            ]);
    }
}
