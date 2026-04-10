<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `students` table.
 *
 * @property int         $id
 * @property string      $email
 * @property string      $password_hash
 * @property string|null $name
 * @property string|null $student_id
 * @property string|null $phone
 */
class Student extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $table = 'students';

    protected $fillable = [
        'email',
        'password_hash',
        'name',
        'student_id',
        'phone',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * Lost reports submitted by this student.
     * Linked via items.user_id = students.email.
     */
    public function lostReports()
    {
        return $this->hasMany(Item::class, 'user_id', 'email')
                    ->where('id', 'LIKE', 'REF-%');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'student_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'recipient_id')
                    ->where('recipient_type', 'student');
    }
}
