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
class Site extends Model
{
    use HasEagerLimit;
    use HasFactory;

    protected $table = 'sites';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * Relationship site (N:M) servers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function servers()
    {
        return $this->belongsToMany(Server::class, 'server_site', 'site_id', 'server_id')->withTimestamps();
    }

    /**
     * Default ordering of the model.
     *
     * Order: name asc
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Previous record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function previous()
    {
        return Site::select('id')
        ->whereRaw('name < (select name from sites where id = ?)', [$this->id])
        ->orderBy('name', 'desc')
        ->take(1);
    }

    /**
     * Next record.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function next()
    {
        return Site::select('id')
        ->whereRaw('name > (select name from sites where id = ?)', [$this->id])
        ->orderBy('name', 'asc')
        ->take(1);
    }

    /**
     * It saves the site in the database and synchronizes its servers in an
     * atomic operation, that is, all or nothing.
     *
     * @param array|int|null $servers server ids
     *
     * @return bool
     */
    public function atomicSaveWithServers(mixed $servers)
    {
        try {
            DB::transaction(function () use ($servers) {
                $this->save();

                $this->servers()->sync($servers);
            });

            return true;
        } catch (\Throwable $th) {
            Log::error(
                __('Site update failed'),
                [
                    'servers' => $servers,
                    'exception' => $th,
                ]
            );

            return false;
        }
    }
}
