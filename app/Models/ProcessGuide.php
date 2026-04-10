<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `process_guides` table.
 *
 * @property int         $id
 * @property string      $title
 * @property string      $section             report_lost | search_found | claim_item
 * @property int         $step_number
 * @property string      $instruction
 * @property int|null    $estimated_time_minutes
 * @property array|null  $faq                 JSON array of {question, answer}
 * @property array|null  $troubleshooting     JSON array of {issue, solution}
 */
class ProcessGuide extends Model
{
    use HasFactory;

    protected $table = 'process_guides';

    protected $fillable = [
        'title',
        'section',
        'step_number',
        'instruction',
        'estimated_time_minutes',
        'faq',
        'troubleshooting',
    ];

    protected $casts = [
        'faq'             => 'array',
        'troubleshooting' => 'array',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeBySection($query, string $section)
    {
        return $query->where('section', $section)->orderBy('step_number');
    }
}
