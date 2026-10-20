<?php

declare(strict_types=1);

namespace App\Form\Type\ProductCMS;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class ProductCMSFaqQuestionTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $validationCallback = static function ($value, ExecutionContextInterface $context) use ($options): void {
            if ('[fr_FR]' === $options['property_path'] && empty($value)) {
                $context
                    ->buildViolation('This value should not be blank.')
                    ->atPath($context->getPropertyPath())
                    ->addViolation();
            }
        };

        $builder
            ->add('question', TextType::class, [
                'required' => true,
                'label' => 'app.form.product.cms.cms_faq.question',
                'constraints' => [
                    new Callback([
                        'groups' => $this->validationGroups,
                        'callback' => $validationCallback,
                    ]),
                ],
            ])
            ->add('answer', TextareaType::class, [
                'required' => true,
                'label' => 'app.form.product.cms.cms_faq.answer',
                'constraints' => [
                    new Callback([
                        'groups' => $this->validationGroups,
                        'callback' => $validationCallback,
                    ]),
                ],
            ]);
    }
}
