<?php

namespace App\Enum;

enum PermissionsEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case CLIENT = 'client';

    public static function labels()
    {
        return [
            self::SUPER_ADMIN->value => 'Super Admin',
            self::CLIENT->value => 'Cliente',
        ];
    }

    public static function accessPanelSupport()
    {
        return [
            self::SUPER_ADMIN,
        ];
    }
}
