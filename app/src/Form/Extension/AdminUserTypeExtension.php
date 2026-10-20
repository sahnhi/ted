<?php

namespace App\Form\Extension;

use App\Form\Type\LocaleType;
use App\Model\Role;
use Sylius\Bundle\CoreBundle\Form\Type\User\AdminUserType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class AdminUserTypeExtension extends AbstractTypeExtension
{
    private $roleHierarchyContainer;

    public function __construct(
        private readonly Security $security,
        private readonly ContainerInterface $container
    ) {
        $this->roleHierarchyContainer = $this->container->getParameter('security.role_hierarchy.roles');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \LogicException('User must be authenticated');
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user): void {
            $availableRoles = $this->getAvailableRoles($user);

            $form = $event->getForm();
            $form->add('roles', ChoiceType::class, [
                'choices' => $availableRoles,
                'expanded' => true,
                'multiple' => true,
                'label' => 'sylius.form.user.roles',
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();

            if (count($data['roles']) > 1) {
                $mainRole[] = $this->getOneRole($data);
                $data['roles'] = $mainRole;
                $event->setData($data);
            }
        });

        $builder->add('localeCode', LocaleType::class);
    }

    private function getOneRole(array $data)
    {
        $rolesPosition = [];

        foreach ($data['roles'] as $role) {
            $rolesPosition[$role] = array_search($role, array_keys($this->roleHierarchyContainer));
        }

        return $mainRole[] = array_search(min($rolesPosition), $rolesPosition);
    }

    private function getAvailableRoles(UserInterface $user): array
    {
        $availableRoles = [];

        foreach ($user->getRoles() as $userRole) {
            $availableRoles[Role::getName($userRole)] = $userRole;
            $roles = $this->roleHierarchyContainer[$userRole];
            foreach ($roles as $role) {
                if ('ROLE_ALLOWED_TO_SWITCH' !== $role) {
                    $availableRoles[Role::getName($role)] = $role;
                }
            }
        }

        return $availableRoles;
    }

    public static function getExtendedTypes(): iterable
    {
        return [AdminUserType::class];
    }
}
