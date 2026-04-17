<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `items` table.
 *
 * The items table stores BOTH found items (id starts with 'UB') and lost reports
 * (id starts with 'REF-'). Use the provided local scopes to distinguish them.
 *
 * @property string      $id
 * @property string|null $user_id            Student email or student_number@ub.edu.ph
 * @property string|null $item_type          Category
 * @property string|null $color
 * @property string|null $brand
 * @property string|null $found_at
 * @property string|null $found_by
 * @property string|null $date_encoded
 * @property string|null $date_lost
 * @property string|null $item_description   May contain embedded key:value metadata lines
 * @property string|null $storage_location
 * @property string|null $image_data         Base64 data URL
 * @property string      $status
 * @property string|null $disposal_deadline
 * @property string|null $matched_barcode_id Reference to matched found item
 */
class Item extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'items';

    /** @var string Non-incrementing string primary key */
    protected $primaryKey = 'id';

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;

    /** @var array */
    protected $fillable = [
        'id',
        'user_id',
        'item_type',
        'color',
        'brand',
        'found_at',
        'found_by',
        'date_encoded',
        'date_lost',
        'item_description',
        'storage_location',
        'image_data',
        'status',
        'disposal_deadline',
        'matched_barcode_id',
    ];

    /** @var array */
    protected $casts = [
        'date_encoded'     => 'date',
        'date_lost'        => 'date',
        'disposal_deadline' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function student()
    {
        // user_id stores the student's email
        return $this->belongsTo(Student::class, 'user_id', 'email');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'found_item_id');
    }

    public function lostReportClaims()
    {
        return $this->hasMany(Claim::class, 'lost_report_id');
    }

    public function itemMatches()
    {
        return $this->hasMany(ItemMatch::class, 'found_item_id');
    }

    // ── Local Scopes ───────────────────────────────────────────────────────

    /** Found items only (UBxxxxx IDs). */
    public function scopeFoundItems($query)
    {
        return $query->where('id', 'NOT LIKE', 'REF-%');
    }

    /** Lost reports only (REF-xxxxxxxxxx IDs). */
    public function scopeLostReports($query)
    {
        return $query->where('id', 'LIKE', 'REF-%');
    }

    /** Filter by status. */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /** Filter by category (item_type). */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('item_type', $category);
    }

    /** Items with status NOT in Claimed/Resolved/Cancelled/Disposed. */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['Claimed', 'Resolved', 'Cancelled', 'Disposed']);
    }

    // ── ID Generation ──────────────────────────────────────────────────────

    /**
     * Generate the next sequential barcode ID (UBxxxxx).
     */
    public static function generateBarcodeId(): string
    {
        $max = static::foundItems()
            ->whereRaw("id REGEXP '^UB[0-9]+$'")
            ->selectRaw("MAX(CAST(SUBSTRING(id, 3) AS UNSIGNED)) AS max_num")
            ->value('max_num');

        $next = ($max !== null ? (int) $max + 1 : 10001);
        return 'UB' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate the next sequential lost-report reference ID (REF-xxxxxxxxxx).
     */
    public static function generateRefId(): string
    {
        $max = static::lostReports()
            ->selectRaw("MAX(CAST(SUBSTRING(id, 5) AS UNSIGNED)) AS max_num")
            ->value('max_num');

        $next = ($max !== null ? (int) $max + 1 : 1);
        return 'REF-' . str_pad($next, 10, '0', STR_PAD_LEFT);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Display ticket ID for lost reports: TIC- + numeric part (screenshots use TIC-).
     * Returns id as-is for non-REF items.
     */
    public function getDisplayTicketIdAttribute(): string
    {
        if (!str_starts_with($this->id, 'REF-')) {
            return $this->id;
        }
        $num = substr($this->id, 4);
        return 'TIC-' . $num;
    }

    /**
     * Parse embedded metadata from item_description.
     * Embedded lines look like "Key: value\n".
     */
    public function parseDescription(): array
    {
        $desc = $this->item_description ?? '';
        $meta = [];

        foreach ([
            'Student Number', 'Full Name', 'Contact', 'Department', 'Item Type',
            'Owner', 'ID Type', 'Item', 'Encoded By', 'Found By',
        ] as $key) {
            if (preg_match('/^' . preg_quote($key, '/') . ':\s*(.*?)(?:\r?\n|$)/m', $desc, $m)) {
                $val = trim($m[1]);
                if ($val !== '') {
                    $meta[$key] = $val;
                }
            }
        }

        return $meta;
    }

    /**
     * Parse the admin-appended block after "Confirm Item Claim" (see Admin\ClaimController::confirm).
     *
     * @return array{name?: string, email?: string, contact?: string, date_accomplished?: string}|null
     */
    public function parseClaimRecord(): ?array
    {
        $desc = $this->item_description ?? '';
        if ($desc === '' || ! str_contains($desc, '--- Claim Record ---')) {
            return null;
        }

        $after = strstr($desc, '--- Claim Record ---');
        if ($after === false) {
            return null;
        }

        $out = [];
        $map = [
            'Claimed By' => 'name',
            'Email' => 'email',
            'Contact' => 'contact',
            'Date Accomplished' => 'date_accomplished',
        ];
        foreach ($map as $label => $key) {
            if (preg_match('/^' . preg_quote($label, '/') . ':\s*(.+?)(?:\n|$)/m', $after, $m)) {
                $val = trim($m[1]);
                if ($val !== '') {
                    $out[$key] = $val;
                }
            }
        }

        return $out === [] ? null : $out;
    }

    /**
     * Found-item types listed under Recovered IDs (Guest tab): surrendered IDs and
     * Document & Identification encodes from Encode Item.
     */
    public static function isFoundGuestTabCategory(?string $itemType): bool
    {
        return $itemType === 'ID & Nameplate' || $itemType === 'Document & Identification';
    }

    /**
     * Compute the retention deadline for this found item.
     * Guest-tab categories: 1 year; other internal categories: 2 years.
     */
    public function retentionEndDate(): ?\Carbon\Carbon
    {
        $base = $this->date_encoded ?? $this->created_at;
        if (! $base) {
            return null;
        }

        $years = self::isFoundGuestTabCategory($this->item_type) ? 1 : 2;

        return \Carbon\Carbon::parse($base)->addYears($years);
    }

    /**
     * Whether this REF- row was created from the student portal (vs admin encoding on behalf).
     */
    public function isStudentSubmittedLostReport(): bool
    {
        if (! str_starts_with((string) $this->id, 'REF-')) {
            return false;
        }

        return ActivityLog::where('item_id', $this->id)
            ->where('action', 'lost_report')
            ->where('actor_type', 'student')
            ->exists();
    }

    /**
     * Internal found item: admin must wait for student claim acknowledgement when a
     * student-submitted lost report is linked to this found item.
     */
    public function requiresStudentClaimIntentBeforeAdminClaim(): bool
    {
        if ($this->item_type === 'ID & Nameplate') {
            return false;
        }

        foreach (static::lostReports()->where('matched_barcode_id', $this->id)->cursor() as $lost) {
            if ($lost->isStudentSubmittedLostReport()) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when every linked student-submitted lost report has a pending/approved Claim row.
     */
    public function hasStudentClaimIntentForAdminClaim(): bool
    {
        if (! $this->requiresStudentClaimIntentBeforeAdminClaim()) {
            return true;
        }

        foreach (static::lostReports()->where('matched_barcode_id', $this->id)->cursor() as $lost) {
            if (! $lost->isStudentSubmittedLostReport()) {
                continue;
            }
            $has = Claim::where('found_item_id', $this->id)
                ->where('lost_report_id', $lost->id)
                ->whereIn('status', ['Pending', 'Approved'])
                ->exists();
            if (! $has) {
                return false;
            }
        }

        return true;
    }
}
