<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Role extends Model
{
    use HasFactory;
    use HasEagerLimit;

    protected $table = 'roles';

    public $incrementing = false;

    public const ADMINISTRATOR = 1000;
    public const INSTITUTIONALMANAGER = 1100;
    public const DEPARTMENTMANAGER = 1200;
    public const ORDINARY = 1300;

    public const VIEWANY = 100001;
    public const VIEW = 100002;
    public const UPDATE = 100003;

    /**
     * Relacionamento perfil (N:M) permissões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')->withTimestamps();
    }

    /**
     * Relacionamento perfil (1:N) usuários.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Registro anterior ao id informado.
     *
     * @param int $id id do modelo
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function previous(int $id)
    {
        return self::select('id')
        ->where('id', '<', $id)
        ->orderBy('id', 'desc')
        ->take(1);
    }

    /**
     * Registro posterior ao id informado.
     *
     * @param int $id id do modelo
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function next(int $id)
    {
        return self::select('id')
        ->where('id', '>', $id)
        ->orderBy('id', 'asc')
        ->take(1);
    }

    /**
     * Atualiza um perfil no banco de dados e syncroniza suas permissões.
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
