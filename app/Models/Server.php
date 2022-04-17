<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Server extends Model
{
    use HasFactory;

    protected $table = 'servers';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * Impressões gerenciadas por um determinado servidor de impressão.
     *
     * Relacionamento servidor de impressão (1:N) impressões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'server_id', 'id');
    }

    /**
     * Relacionamento server (N:M) site.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'server_site', 'server_id', 'site_id')->withTimestamps();
    }
}
