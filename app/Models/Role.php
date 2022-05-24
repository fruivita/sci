<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Role extends Model
{
    use HasEagerLimit;
    use HasFactory;

    protected $table = 'roles';

    public $incrementing = false;

    public const ADMINISTRATOR = 9000;
    public const BUSINESSMANAGER = 8000;
    public const INSTITUTIONALMANAGER = 7000;
    public const DEPARTMENTMANAGER = 6000;
    public const ORDINARY = 1000;

    /**
     * Relationship role (N:M) permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')->withTimestamps();
    }

    /**
     * Relationship role (1:N) users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Default ordering of the model.
     *
     * This ordering should not be changed, as the delegation process takes it
     * to determine the most privileged (lowest id) and least privileged
     * (highest id) roles.
     *
     * If this ordering changes, should review the delegation process for
     * necessary adjustments.
     *
     * Order: Id desc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('id', 'desc');
    }

    /**
     * Roles available to assign to another user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvaiableToAssign($query)
    {
        return $query->where('id', '<=', auth()->user()->role_id);
    }

    /**
     * Previous record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return self::select('id')
        ->where('id', '<', $this->id)
        ->orderBy('id', 'desc')
        ->take(1);
    }

    /**
     * Next record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return self::select('id')
        ->where('id', '>', $this->id)
        ->orderBy('id', 'asc')
        ->take(1);
    }

    /**
     * It saves the role in the database and synchronizes its permissions in an
     * atomic operation, that is, all or nothing.
     *
     * @param array|int|null $permissions permissions ids
     *
     * @return bool
     */
    public function atomicSaveWithPermissions(mixed $permissions)
    {
        try {
            DB::transaction(function () use ($permissions) {
                $this->save();

                $this->permissions()->sync($permissions);
            });

            return true;
        } catch (\Throwable $th) {
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
