<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API CRUD for notifications.
 * Ported from api/routes/notifications.php.
 */
class NotificationController extends Controller
{
    /** GET /api/notifications */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::query()->orderByDesc('created_at');

        if (session('admin_id')) {
            $query->forAdmin(session('admin_id'));
        } elseif (session('student_id')) {
            $query->forStudent(session('student_id'));
        }

        if ($request->boolean('unread')) {
            $query->unread();
        }

        $limit = min((int) $request->query('limit', 30), 100);
        $notifications = $query->limit($limit)->get();

        return response()->json(['ok' => true, 'data' => $notifications]);
    }

    /** GET /api/notifications/unread-count */
    public function unreadCount(): JsonResponse
    {
        $query = Notification::unread();

        if (session('admin_id')) {
            $query->forAdmin(session('admin_id'));
        } elseif (session('student_id')) {
            $query->forStudent(session('student_id'));
        }

        return response()->json(['ok' => true, 'count' => $query->count()]);
    }

    /** PATCH /api/notifications/{id}/read — mark a single notification read */
    public function markRead(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);
        return response()->json(['ok' => true]);
    }

    /** PATCH /api/notifications/read-all — mark all as read */
    public function markAllRead(): JsonResponse
    {
        $query = Notification::unread();

        if (session('admin_id')) {
            $query->forAdmin(session('admin_id'));
        } elseif (session('student_id')) {
            $query->forStudent(session('student_id'));
        }

        $query->update(['is_read' => true]);
        return response()->json(['ok' => true]);
    }

    /** DELETE /api/notifications/{id} */
    public function destroy(int $id): JsonResponse
    {
        Notification::findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }

    /** POST /api/notifications — create a notification (admin only) */
    public function store(Request $request): JsonResponse
    {
        if (!session('admin_id')) {
            return response()->json(['ok' => false, 'error' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'recipient_id'   => 'required|integer',
            'recipient_type' => 'required|in:admin,student',
            'type'           => 'required|string|max:50',
            'title'          => 'required|string|max:255',
            'message'        => 'required|string',
            'related_id'     => 'nullable|string|max:50',
        ]);

        $notification = Notification::create($validated + ['is_read' => false]);

        return response()->json(['ok' => true, 'data' => $notification]);
    }
}
