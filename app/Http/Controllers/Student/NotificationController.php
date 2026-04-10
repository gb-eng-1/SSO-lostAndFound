<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * Student notifications list.
 * Ported from STUDENT/StudentNotifications.php.
 */
class NotificationController extends Controller
{
    /** GET /student/notifications */
    public function index()
    {
        $studentId = session('student_id');

        $notifications = Notification::forStudent($studentId)
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Notification::forStudent($studentId)->unread()->count();

        return view('student.notifications', compact('notifications', 'unreadCount'));
    }

    /** GET /student/notifications/recent — lightweight JSON for bell dropdown */
    public function recent()
    {
        $studentId = session('student_id');

        $notifications = Notification::forStudent($studentId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'type', 'title', 'message', 'is_read', 'created_at', 'related_id']);

        $unread = Notification::forStudent($studentId)->unread()->count();

        return response()->json([
            'ok'              => true,
            'unread_count'    => $unread,
            'notifications'   => Notification::toBellPayload($notifications, false),
        ]);
    }

    /** POST /student/notifications/{id}/read */
    public function markRead(Request $request, int $id)
    {
        Notification::where('id', $id)
            ->where('recipient_id', session('student_id'))
            ->where('recipient_type', 'student')
            ->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    /** POST /student/notifications/read-all */
    public function markAllRead(Request $request)
    {
        Notification::forStudent((int) session('student_id'))->unread()->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    /** DELETE /student/notifications/{id} */
    public function destroy(int $id)
    {
        Notification::where('id', $id)
            ->where('recipient_id', session('student_id'))
            ->where('recipient_type', 'student')
            ->delete();

        return back()->with('success', 'Notification deleted.');
    }
}
