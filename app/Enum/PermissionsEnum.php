<?php

namespace App\Enum;

enum PermissionsEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case CLIENT = 'client';

    public static function labels()
    {
        return [
            self::SUPER_ADMIN->value => 'Super Admin',
            self::ADMIN->value => 'Admin',
            self::CLIENT->value => 'Cliente',
        ];
    }

    public static function accessPanelSupport()
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
        ];
    }
}
