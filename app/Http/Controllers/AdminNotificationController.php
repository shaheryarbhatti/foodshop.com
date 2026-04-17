<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        $notifications = AdminNotification::query()
            ->with('order:id,order_number')
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(8)
            ->get();

        return response()->json([
            'unread_count' => AdminNotification::query()
                ->where('user_id', $user->id)
                ->where('is_read', false)
                ->count(),
            'latest_id' => (int) ($notifications->first()->id ?? 0),
            'notifications' => $notifications->map(function (AdminNotification $notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'created_at' => optional($notification->created_at)->diffForHumans(),
                    'open_url' => route('admin.notifications.open', $notification),
                    'order_number' => optional($notification->order)->order_number,
                ];
            })->values(),
        ]);
    }

    public function open(Request $request, AdminNotification $notification): RedirectResponse
    {
        abort_unless($request->user() && $notification->user_id === $request->user()->id, 403);

        $notification->markAsRead();

        if ($notification->order_id) {
            return redirect()->route('orders.edit', $notification->order_id);
        }

        return redirect()->route('orders.manage');
    }
}
