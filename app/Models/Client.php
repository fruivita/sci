<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Qualquer dispositivo capaz de solicitar uma impressão.
 *
 * Ex.: computador, notebook, tablet.
 *
 * @see https://laravel.com/docs/eloquent
 */
class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * Impressões vindas de um determinado cliente.
     *
     * Relacionamento cliente (1:N) impressões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'client_id', 'id');
    }
}
