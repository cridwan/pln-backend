<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Hash;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: ResponseMiddleware::class)]
#[Group('Auth')]
class AuthController extends Controller
{
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

        $token = $user->createToken(config('passport.private_key'))->accessToken;

        return [
            ...$user->toArray(),
            'token' => $token
        ];
    }
}
