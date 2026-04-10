<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `activity_log` table.
 *
 * @property int         $id
 * @property string      $action       e.g. encoded, matched, claimed
 * @property int|null    $actor_id
 * @property string      $actor_type   admin | student | system
 * @property string|null $item_id
 * @property string|null $details
 */
class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_log';

    const UPDATED_AT = null;

    protected $fillable = [
        'action',
        'actor_id',
        'actor_type',
        'item_id',
        'details',
    ];

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Record an activity entry. Failures are silently ignored so they never
     * break the main request flow.
     */
    public static function record(
        string $action,
        ?string $itemId = null,
        ?string $details = null,
        ?int $actorId = null,
        string $actorType = 'system'
    ): void {
        try {
            static::create([
                'action'     => $action,
                'actor_id'   => $actorId,
                'actor_type' => $actorType,
                'item_id'    => $itemId,
                'details'    => $details,
            ]);
        } catch (\Throwable $e) {
            // Non-fatal — activity logging should never interrupt the main flow
        }
    }
}
