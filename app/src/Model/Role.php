<?php

namespace App\Model;

use Sylius\Component\Core\Model\AdminUserInterface;

final class Role
{
    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_VIEWER,
        self::ROLE_ACCOUNT,
        AdminUserInterface::DEFAULT_ADMIN_ROLE,
    ];

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_MANAGER = 'ROLE_MANAGER';
    public const ROLE_VIEWER = 'ROLE_VIEWER';
    public const ROLE_ACCOUNT = 'ROLE_ACCOUNT';

    public static function getName($role)
    {
        switch ($role) {
            case AdminUserInterface::DEFAULT_ADMIN_ROLE:
                return 'Super Admin';
            case self::ROLE_ADMIN:
                return 'Admin';
            case self::ROLE_MANAGER:
                return 'Gestionnaire';
            case self::ROLE_VIEWER:
                return 'Viewer';
            case self::ROLE_ACCOUNT:
                return 'Account';
        }
    }
}
