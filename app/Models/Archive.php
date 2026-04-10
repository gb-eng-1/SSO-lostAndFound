<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `archives` table.
 * Immutable snapshot of a resolved claim.
 *
 * @property int         $id
 * @property string      $reference_id
 * @property string      $found_item_id
 * @property int         $student_id
 * @property string      $claimant_name
 * @property string      $claimant_email
 * @property string|null $claimant_phone
 * @property array       $item_details       JSON snapshot of the found item at resolution time
 * @property string|null $proof_photo        Base64 data URL
 * @property \DateTime   $claim_date
 * @property \DateTime   $resolution_date
 */
class Archive extends Model
{
    use HasFactory;

    protected $table = 'archives';

    public $timestamps = false;

    protected $fillable = [
        'reference_id',
        'found_item_id',
        'student_id',
        'claimant_name',
        'claimant_email',
        'claimant_phone',
        'item_details',
        'proof_photo',
        'claim_date',
        'resolution_date',
    ];

    protected $casts = [
        'item_details'    => 'array',
        'claim_date'      => 'datetime',
        'resolution_date' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
