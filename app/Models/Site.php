<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Site extends Model
{
    use HasFactory;
    use HasEagerLimit;

    protected $table = 'sites';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * Relacionamento site (N:M) server.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function servers()
    {
        return $this->belongsToMany(Server::class, 'server_site', 'site_id', 'server_id')->withTimestamps();
    }

    /**
     * Ordenação padrão do modelo.
     *
     * Ordem: name asc
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
     * Registro anterior.
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
     * Registro posterior.
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
}
