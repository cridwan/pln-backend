<?php

namespace App\Http\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;

class PermissionRoleMiddleware extends RoleOrPermissionMiddleware
{
    /**
     * Specify the permission and guard for the middleware.
     *
     * @param  array<string|\App\Enums\PermissionEnum>|string  $permission
     * @param  string|null  $guard
     * @return string
     */
    public static function using($permission, $guard = null)
    {
        $permissionString = is_string($permission) ? $permission : implode('|', array_map(function ($permission) {
            if ($permission instanceof BackedEnum) {
                return $permission->value;
            }

            return $permission;
        }, $permission));
        $args = is_null($guard) ? $permissionString : "$permissionString,$guard";

        return static::class . ':' . $args;
    }
}
