<?php

namespace App\Enum;

enum PermissionsEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case COMMUNITY_MANAGER = 'community_manager';
    case CLIENT = 'client';

    public static function labels()
    {
        return [
            self::SUPER_ADMIN->value => 'Super Admin',
            self::ADMIN->value => 'Admin',
            self::COMMUNITY_MANAGER->value => 'Gerenciador da Comunidade',
            self::CLIENT->value => 'Cliente',
        ];
    }

    public static function accessPanelSupport()
    {
        return [
            self::SUPER_ADMIN,
            self::COMMUNITY_MANAGER,
            self::ADMIN,
        ];
    }
}
