<?php

namespace App\Form\Type;

use App\Doctrine\Entity\AdminParameter;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class DaysDelayReviewType extends AbstractResourceType
{
    public function __construct(array $validationGroups = [])
    {
        parent::__construct(AdminParameter::class, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('key', HiddenType::class);
        $builder->add('value', IntegerType::class, [
            'label' => 'app.ui.days_delay_review',
        ]);
    }
}
