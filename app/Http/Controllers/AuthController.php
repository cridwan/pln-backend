<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Exceptions\AnauthenticateException;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\LoginRequest;
use App\Models\Permission;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Hash;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: ResponseMiddleware::class)]
#[Group('Auth')]
class AuthController extends Controller
{
    /**
     * login
     */
    #[Route(method: 'post')]
    public function login(LoginRequest $request)
    {
        $user = User::where("email", $request->email)->first();

        if (!$user) {
            throw new BadRequestException("Username atau password tidak ditemukan");
        }

        if (!Hash::check($request->password, $user->password)) {
            throw new BadRequestException("Username atau password tidak ditemukan");
        }

        $token = $user->createToken(config('passport.access_token_key'))->accessToken;

        $permissions = $user->hasRole(RoleEnum::SUPERUSER->value) ? Permission::latest()->pluck('name')->toArray() : $user->getPermissionNames();

        return [
            'token' => $token,
            ...$user->toArray(),
            'permissions' => $permissions
        ];
    }

    #[DoNotDiscover]
    public function anauthenticate()
    {
        throw new AnauthenticateException('Anauthenticate');
    }
}
