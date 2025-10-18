<?php

namespace App\Http\Controllers;

use App\Data\NotificationData;
use App\Enums\AuthPermissionEnum;
use App\Models\Notification;
use App\Services\NotificationService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

#[Group('Notification')]
class NotificationController extends Controller implements HasMiddleware
{
    #[DoNotDiscover]
    public static function middleware()
    {
        return [
            new Middleware(AuthPermissionEnum::AUTH_API->value, except: ['index', 'show']),
        ];
    }

    #[DoNotDiscover]
    public function __construct(public NotificationService $notificationService)
    {
    }

    /**
     * Get List Notification
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        return $this->notificationService->pagination();
    }

    /**
     * Summary of latest
     * @return \Illuminate\Database\Eloquent\Collection<int, Notification>
     */
    #[Route(method: 'get', uri: 'latest/lists')]
    public function latest(Request $request)
    {
        return $this->notificationService->latest($request);
    }

    /**
     * Summary of markAsRead
     * @param \App\Models\Notification $notification
     * @return Notification
     */
    public function markAsRead(Notification $notification)
    {
        return $this->notificationService->markAsRead($notification);
    }

    /**
     * Summary of destroy
     * @param \App\Models\Notification $notification
     * @return bool|null
     */
    public function destroy(Notification $notification)
    {
        return $this->notificationService->destroy($notification);
    }

    /**
     * Summary of show
     * @param \App\Models\Notification $notification
     * @return Notification
     */
    public function show(Notification $notification)
    {
        return $this->notificationService->show($notification);
    }

    /**
     * Summary of update
     * @param \App\Models\Notification $notification
     * @param \Illuminate\Http\Request $request
     * @return Notification
     */
    public function update(Notification $notification, Request $request)
    {
        $data = NotificationData::fromRequest($request);

        return $this->notificationService->update($notification, $data);
    }

    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return Notification
     */
    public function store(Request $request)
    {
        $data = NotificationData::fromRequest($request);

        return $this->notificationService->store($data);
    }
}
