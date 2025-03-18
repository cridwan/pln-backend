<?php

namespace App\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Middleware\RoleMiddleware as BaseRoleMiddleware;

class RoleMiddleware extends BaseRoleMiddleware
{
    /**
     * Specify the role and guard for the middleware.
     *
     * @param  array<string|\App\Enums\RoleEnum>|string  $role
     * @param  string|null  $guard
     * @return string
     */
    public static function using($role, $guard = null)
    {
        $roleString = is_string($role) ? $role : implode('|', array_map(function ($role) {
            if ($role instanceof  BackedEnum) {
                return $role->value;
            }

            return $role;
        }, $role));
        $args = is_null($guard) ? $roleString : "$roleString,$guard";

        return static::class . ':' . $args;
    }
}
