<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `matches` table.
 * A formal pairing between a lost report and a found item.
 *
 * @property int         $id
 * @property string      $lost_report_id
 * @property string      $found_item_id
 * @property float       $confidence_score  0-100
 * @property array|null  $matching_criteria JSON
 * @property string      $status            Pending_Review | Approved | Rejected
 */
class ItemMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'lost_report_id',
        'found_item_id',
        'confidence_score',
        'matching_criteria',
        'status',
    ];

    protected $casts = [
        'matching_criteria' => 'array',
        'confidence_score'  => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function lostReport()
    {
        return $this->belongsTo(Item::class, 'lost_report_id');
    }

    public function foundItem()
    {
        return $this->belongsTo(Item::class, 'found_item_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopePendingReview($query)
    {
        return $query->where('status', 'Pending_Review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }
}
