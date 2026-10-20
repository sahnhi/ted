<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use Sylius\Bundle\CoreBundle\Form\Type\Promotion\Filter\ProductFilterConfigurationType;
use Sylius\Component\Core\Model\ProductTranslation;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProductFilterConfigurationTypeTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
                $data = $event->getData();

                if (!is_array($data) || !isset($data['products'])) {
                    return;
                }

                foreach ($data['products'] as $productCode) {
                    /** @var Product|null $product */
                    $product = $this->productRepository->findOneBy(['code' => $productCode]);
                    if (null === $product) {
                        continue;
                    }

                    /** @var ProductTranslation $translation */
                    foreach ($product->getTranslations() as $localeCode => $translation) {
                        $data['product_names'][$productCode][$localeCode] = $translation->getName();
                    }
                }

                $event->setData($data);
            });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductFilterConfigurationType::class];
    }
}
