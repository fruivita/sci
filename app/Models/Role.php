<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    public $incrementing = false;

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }
}