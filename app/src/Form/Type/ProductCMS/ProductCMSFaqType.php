<?php

declare(strict_types=1);

namespace App\Form\Type\ProductCMS;

use App\Doctrine\Entity\ProductCMSFaq;
use App\Doctrine\Entity\ProductCMSFaqQuestion;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class ProductCMSFaqType extends AbstractResourceType
{
    private const REQUIRED_TRANSLATION_LOCALE = 'fr_FR';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ProductCMSFaqTranslationType::class,
                'required' => false,
            ])
            ->add('questions', CollectionType::class, [
                'entry_type' => ProductCMSFaqQuestionType::class,
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'app.form.product.cms.cms_faq.question',
                'delete_empty' => function (ProductCMSFaqQuestion $question): bool {
                    if ($question->getTranslations()->isEmpty()) {
                        return true;
                    }

                    $requiredTranslation = $question->getTranslation(self::REQUIRED_TRANSLATION_LOCALE);
                    if (empty($requiredTranslation->getQuestion())) {
                        return true;
                    }

                    return false;
                },
            ]);

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
                /** @var ProductCMSFaq $data */
                $data = $event->getData();

                $requiredTranslation = $data->getTranslation(self::REQUIRED_TRANSLATION_LOCALE);
                if (empty($requiredTranslation->getTitle()) || $data->getTranslations()->isEmpty()) {
                    $event->setData(null);
                }
            });
    }
}
