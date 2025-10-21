<?php

namespace App\Http\Controllers;

use App\Data\NotificationData;
use App\Enums\AuthPermissionEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\RoleEnum;
use App\Exceptions\AnauthenticateException;
use App\Exceptions\BadRequestException;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserUpdateProfileRequest;
use App\Models\Permission;
use App\Models\User;
use App\Notifications\PasswordChangeAlert;
use App\Services\NotificationService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: ResponseMiddleware::class)]
#[Group('Auth')]
class AuthController extends Controller implements HasMiddleware
{

    #[DoNotDiscover]
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, only: ['me', 'profile'])
        ];
    }

    /**
     * Summary of me
     * @return User
     */
    #[Route(method: 'get')]
    public function me()
    {
        return auth()->user();
    }

    /**
     * Summary of profile
     * @return User
     */
    #[Route(method: 'put')]
    public function profile(UserUpdateProfileRequest $request)
    {
        $user = auth()->user();
        $request->merge([
            'password' => $request->filled('password') ? Hash::make($request->string('password')) : auth()->user()->password
        ]);
        $user->fill($request->validated());
        $user->save();
    }


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

        if ($user->first_create) {
            $this->notificationService->store(new NotificationData(
                title: 'Pemberitahuan',
                body: 'silahkan mengganti password akun baru anda di profile',
                type: NotificationTypeEnum::INFO,
                receiver_id: $user->id,
                sender_id: $user->id,
                is_read: false,
                uri: '',
                summary: 'Halo ' . $user->name . ', demi keamana silahkan untuk mengganti password akun anda di profile.'
            ));
            $user->notify(new PasswordChangeAlert($user));

            $user->first_create = false;
            $user->updateQuietly();
        }

        return [
            'token' => $token,
            ...$user->toArray(),
            'permissions' => $permissions
        ];
    }

    /**
     * unauthenticate
     */
    #[Route(method: 'get', name: 'login')]
    public function anauthenticate()
    {
        throw new AnauthenticateException('Anauthenticate', 401);
    }
}
