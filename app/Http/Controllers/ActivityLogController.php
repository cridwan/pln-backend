<?php

namespace App\Http\Controllers;

use App\Enums\AuthPermissionEnum;
use App\Http\Requests\ActivityLogRequest;
use App\Models\ActivityLog;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Activity Log')]
class ActivityLogController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, only: ['sync']),
        ];
    }

    /**
     * sync activity log
     */
    #[Route(method: 'POST')]
    public function sync(ActivityLogRequest $request)
    {
        ActivityLog::updateOrCreate($request->validated(), [
            'updated_id' => auth()->user()->id
        ]);

        return ['message' => 'Sync successfully'];
    }
}
