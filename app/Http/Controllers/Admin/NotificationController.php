<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * Admin notifications list page.
 * Ported from ADMIN/AdminNotifications.php.
 */
class NotificationController extends Controller
{
    /** GET /admin/notifications */
    public function index()
    {
        $adminId = session('admin_id');

        $notifications = Notification::forAdmin($adminId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.notifications', compact('notifications'));
    }

    /** GET /admin/notifications/recent — lightweight JSON for bell dropdown */
    public function recent()
    {
        $adminId = session('admin_id');

        $notifications = Notification::forAdmin($adminId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'type', 'title', 'message', 'is_read', 'created_at', 'related_id']);

        $unread = Notification::forAdmin($adminId)->unread()->count();

        return response()->json([
            'ok'              => true,
            'unread_count'    => $unread,
            'notifications'   => Notification::toBellPayload($notifications),
        ]);
    }

    /** POST /admin/notifications/{id}/read — mark one as read */
    public function markRead(Request $request, int $id)
    {
        Notification::where('id', $id)
            ->where('recipient_id', session('admin_id'))
            ->where('recipient_type', 'admin')
            ->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    /** POST /admin/notifications/read-all */
    public function markAllRead(Request $request)
    {
        Notification::forAdmin((int) session('admin_id'))->unread()->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    /** DELETE /admin/notifications/{id} */
    public function destroy(int $id)
    {
        Notification::where('id', $id)
            ->where('recipient_id', session('admin_id'))
            ->where('recipient_type', 'admin')
            ->delete();

        return back()->with('success', 'Notification deleted.');
    }
}
