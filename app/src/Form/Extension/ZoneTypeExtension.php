<?php

namespace App\Form\Extension;

use App\Doctrine\Entity\ZipCode;
use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\AddressingBundle\Form\Type\ZoneType;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class ZoneTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('message', TextType::class, [
            'label' => 'sylius.form.zone.message',
            'required' => false,
        ])->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var ZoneInterface $zone */
            $zone = $event->getData();
            $form = $event->getForm();

            if (ZoneInterface::TYPE_PROVINCE === $zone->getType()) {
                $splitted = explode('_', (string) $zone->getCode());
                $country = array_shift($splitted);

                $form->add('zipCode', EntityType::class, [
                    'class' => ZipCode::class,
                    'multiple' => true,
                    'required' => false,
                    'label' => 'sylius.menu.admin.zip_codes',
                    'by_reference' => false,
                    'expanded' => false,
                    'attr' => ['class' => 'select2'],
                    'group_by' => fn ($zipCode, $key, $value): string => substr((string) $zipCode->getCode(), 0, 2),
                    'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('zp')
                              ->orderBy('zp.code', 'ASC')
                              ->where('zp.country = :country')
                              ->setParameter('country', $country),
                ])
                ->add('filter', CheckboxType::class, [
                    'mapped' => false,
                    'required' => false,
                    'data' => true,
                    'label' => 'sylius.form.zone.department_filter',
                ]);
            }
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [ZoneType::class];
    }
}
