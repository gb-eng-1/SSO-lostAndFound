<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Represents a row in the `notifications` table.
 * Used for both admin and student recipients.
 *
 * @property int         $id
 * @property int         $recipient_id
 * @property string      $recipient_type  admin | student
 * @property string      $type            e.g. match_found, claim_approved
 * @property string      $title
 * @property string      $message
 * @property string|null $related_id
 * @property bool        $is_read
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * Disable updated_at — the table only has created_at.
     */
    const UPDATED_AT = null;

    protected $table = 'notifications';

    protected $fillable = [
        'recipient_id',
        'recipient_type',
        'type',
        'title',
        'message',
        'related_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeForAdmin($query, int $adminId)
    {
        return $query->where('recipient_id', $adminId)
                     ->where('recipient_type', 'admin');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('recipient_id', $studentId)
                     ->where('recipient_type', 'student');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Notify the first admin in the database.
     */
    public static function notifyAdmin(string $type, string $title, string $message, ?string $relatedId = null): void
    {
        $adminId = Admin::orderBy('id')->value('id');
        if ($adminId) {
            static::create([
                'recipient_id'   => $adminId,
                'recipient_type' => 'admin',
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'related_id'     => $relatedId,
                'is_read'        => false,
            ]);
        }
    }

    /**
     * Notify a specific student.
     */
    public static function notifyStudent(int $studentId, string $type, string $title, string $message, ?string $relatedId = null): void
    {
        static::create([
            'recipient_id'   => $studentId,
            'recipient_type' => 'student',
            'type'           => $type,
            'title'          => $title,
            'message'        => $message,
            'related_id'     => $relatedId,
            'is_read'        => false,
        ]);
    }

    /**
     * JSON rows for bell dropdown (thumbnail from related Item when available for admin only).
     *
     * @param  Collection<int, Notification>  $notifications
     * @return list<array<string, mixed>>
     */
    public static function toBellPayload(Collection $notifications, bool $includeThumbnails = true): array
    {
        $relatedIds = $notifications->pluck('related_id')->filter()->unique()->values();
        $items        = (! $includeThumbnails || $relatedIds->isEmpty())
            ? collect()
            : Item::whereIn('id', $relatedIds)->get(['id', 'image_data'])->keyBy('id');

        return $notifications->map(function (Notification $n) use ($items, $includeThumbnails) {
            $thumb = null;
            if ($includeThumbnails && $n->related_id && $items->has($n->related_id)) {
                $img = $items[$n->related_id]->image_data;
                if (is_string($img) && str_starts_with($img, 'data:') && strlen($img) < 120000) {
                    $thumb = $img;
                }
            }

            return [
                'id'              => $n->id,
                'type'            => $n->type,
                'title'           => $n->title,
                'message'         => $n->message,
                'is_read'         => $n->is_read,
                'created_at'      => $n->created_at?->toIso8601String(),
                'time_relative'   => $n->created_at?->diffForHumans(),
                'related_id'      => $n->related_id,
                'thumbnail_url'   => $thumb,
            ];
        })->values()->all();
    }
}
