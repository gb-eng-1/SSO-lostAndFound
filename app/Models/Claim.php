<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `claims` table.
 *
 * @property int         $id
 * @property string      $reference_id       Unique claim identifier (e.g. CLM-xxxxxxxxxx)
 * @property int         $student_id
 * @property string      $found_item_id
 * @property string|null $lost_report_id
 * @property string|null $proof_photo        Base64 data URL
 * @property string|null $proof_description
 * @property string      $status             Pending | Approved | Rejected | Resolved
 * @property \DateTime   $claim_date
 * @property \DateTime|null $resolution_date
 */
class Claim extends Model
{
    use HasFactory;

    protected $table = 'claims';

    protected $fillable = [
        'reference_id',
        'student_id',
        'found_item_id',
        'lost_report_id',
        'proof_photo',
        'proof_description',
        'status',
        'claim_date',
        'resolution_date',
    ];

    protected $casts = [
        'claim_date'      => 'datetime',
        'resolution_date' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function foundItem()
    {
        return $this->belongsTo(Item::class, 'found_item_id');
    }

    public function lostReport()
    {
        return $this->belongsTo(Item::class, 'lost_report_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopeActiveFor(string $foundItemId)
    {
        return $this->where('found_item_id', $foundItemId)
                    ->whereIn('status', ['Pending', 'Approved']);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Generate a unique claim reference ID.
     */
    public static function generateReferenceId(): string
    {
        do {
            $ref = 'CLM-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        } while (static::where('reference_id', $ref)->exists());

        return $ref;
    }
}
