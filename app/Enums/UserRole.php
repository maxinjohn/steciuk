<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Vicar = 'vicar';
    case Editor = 'editor';
    case Member = 'member';

    /**
     * Built-in roles an Admin may assign (excludes Super Admin; custom roles are allowed separately).
     *
     * @return list<string>
     */
    public static function adminAssignableSlugs(): array
    {
        return [
            self::Member->value,
            self::Editor->value,
            self::Admin->value,
            self::Vicar->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function panelRoleSlugs(): array
    {
        return [
            self::SuperAdmin->value,
            self::Admin->value,
            self::Vicar->value,
            self::Editor->value,
        ];
    }
}
