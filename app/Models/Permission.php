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
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('id', 'asc');
    }

    /**
     * Registro anterior.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return Permission::select('id')
        ->where('id', '<', $this->id)
        ->orderBy('id', 'desc')
        ->take(1);
    }

    /**
     * Registro posterior.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return Permission::select('id')
        ->where('id', '>', $this->id)
        ->orderBy('id', 'asc')
        ->take(1);
    }

    /**
     * Salva a permissão no banco de dados e syncroniza seus perfis em uma
     * operação atômica, isto é, tudo ou nada.
     *
     * @param array|int|null $roles ids dos perfis
     *
     * @return bool
     */
    public function atomicSaveWithRoles(mixed $roles)
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
