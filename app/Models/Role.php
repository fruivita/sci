<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    public $incrementing = false;

    const ADMINISTRATOR = 1000;
    const INSTITUTIONALMANAGER = 1100;
    const DEPARTMENTMANAGER = 1200;
    const ORDINARY = 1300;

    const VIEWANY = 10000;
    const UPDATE = 13000;

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Atualiza um perfil no banco de dados e syncroniza seus permissões.
     *
     * @param array|int|null $permissions ids das permissões
     *
     * @return bool
     */
    public function updateAndSync(mixed $permissions)
    {
        try {
            DB::beginTransaction();

            $this->save();

            $this->permissions()->sync($permissions);

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error(
                __('Role update failed'),
                [
                    'permissions' => $permissions,
                    'exception' => $th,
                ]
            );

            return false;
        }
    }
}
