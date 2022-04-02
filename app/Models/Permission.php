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

    /**
     * Id da permissão de visualizar todas as permissões.
     *
     * @var int
     */
    public const VIEWANY = 110001;

    /**
     * Id da permissão de visualizar uma permissão.
     *
     * @var int
     */
    public const VIEW = 110002;

    /**
     * Id da permissão de atualizar uma permissão.
     *
     * @var int
     */
    public const UPDATE = 110004;

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
     * Ordenação padrão do modelo.
     *
     * Ordem: Id asc
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('id', 'asc');
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
