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
class Permission extends Model
{
    use HasFactory;
    use HasEagerLimit;

    protected $table = 'permissions';

    public $incrementing = false;

    public const VIEWANY = 110001;
    public const VIEW = 110002;
    public const UPDATE = 110003;

    /**
     * Relacionamento permissão (M:N) perfis.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id')->withTimestamps();
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
     * Atualiza uma permissão no banco de dados e syncroniza seus perfis.
     *
     * @param array|int|null $roles ids dos perfis
     *
     * @return bool
     */
    public function updateAndSync(mixed $roles)
    {
        try {
            DB::beginTransaction();

            $this->save();

            $this->roles()->sync($roles);

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error(
                __('Permission update failed'),
                [
                    'roles' => $roles,
                    'exception' => $th,
                ]
            );

            return false;
        }
    }
}
