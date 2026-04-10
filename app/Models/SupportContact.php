<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `support_contacts` table.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string|null $phone
 * @property string|null $office_location
 * @property string|null $department
 * @property string|null $role
 * @property string|null $office_hours
 */
class SupportContact extends Model
{
    use HasFactory;

    protected $table = 'support_contacts';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'office_location',
        'department',
        'role',
        'office_hours',
    ];
}
