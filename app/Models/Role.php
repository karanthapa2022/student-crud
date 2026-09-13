<?php

namespace App\Models;

use InvalidArgumentException;

final class Role
{
    public const ADMIN = 'admin';
    public const TEACHER = 'teacher';
    public const STUDENT = 'student';
    public const PARENT = 'parent';

    public static function all(): array
    {
        return [
            self::ADMIN,
            self::TEACHER,
            self::STUDENT,
            self::PARENT,
        ];
    }

    public static function validate(string $role): string
    {
        if (!in_array($role, self::all(), true)) {
            throw new InvalidArgumentException('Invalid user role.');
        }

        return $role;
    }

    public static function isValid(?string $role): bool
    {
        return $role !== null && in_array($role, self::all(), true);
    }
}
