<?php

namespace App\Http\Middleware;

use App\Models\User;
use BackedEnum;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\RoleMiddleware as BaseRoleMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;

class RoleMiddleware extends BaseRoleMiddleware
{
    /**
     * Specify the role and guard for the middleware.
     *
     * @param  array<string|\App\Enums\RoleEnum>|string  $role
     * @param  string|null  $guard
     * @return string
     */
    public static function using($role, $guard = 'api')
    {
        $roleString = is_string($role) ? $role : implode('|', array_map(function ($role) {
            if ($role instanceof BackedEnum) {
                return $role->value;
            }

            return $role;
        }, $role));
        $args = is_null($guard) ? $roleString : "$roleString,$guard";
        return (string) 'role' . ':' . $args . ',api';
    }

    public function handle($request, Closure $next, $role, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        $user = $authGuard->user();

        // For machine-to-machine Passport clients
        if (!$user && $request->bearerToken() && config('permission.use_passport_client_credentials')) {
            $user = Guard::getPassportClient($guard);
        }

        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (!method_exists($user, 'hasAnyRole')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        $roles = explode('|', self::parseRolesToString($role));
        if (!$user->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }
}
