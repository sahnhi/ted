<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\ResourceAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

final class ProductVariantAutocompleteChoiceType extends AbstractType
{
    public function __construct(
        private readonly RouterInterface $router
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'resource' => 'sylius.product_variant',
            'choice_name' => 'descriptor',
            'choice_value' => 'code',
            'remote_url' => 'test',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $routeAjaxVariantByName = $this->router->generate('sylius_admin_ajax_variant_by_name_phrase');
        $routeAjaxVariantByCode = $this->router->generate('sylius_admin_ajax_variant_by_code');
        $view->vars['remote_criteria_type'] = 'contains';
        $view->vars['remote_criteria_name'] = 'phrase';
        $view->vars['remote_url'] = $routeAjaxVariantByName;
        $view->vars['load_edit_url'] = $routeAjaxVariantByCode;
    }

    public function getParent(): string
    {
        return ResourceAutocompleteChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'app_promotion_rule_variant_configuration';
    }
}
