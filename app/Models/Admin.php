<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a row in the `admins` table.
 *
 * The password column is named `password_hash` to match the existing schema.
 * We implement Authenticatable manually to map getAuthPassword() to that column.
 *
 * @property int    $id
 * @property string $email
 * @property string $password_hash
 * @property string $name
 * @property string $role
 */
class Admin extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $table = 'admins';

    protected $fillable = [
        'email',
        'password_hash',
        'name',
        'role',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Laravel's Auth system calls getAuthPassword() to retrieve the hashed password.
     * We override it to point to the correct column name.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Return the column used as the unique login identifier.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }
}
