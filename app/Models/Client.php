<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Any device capable of requesting a print.
 *
 * Computer, notebook, tablet, etc.
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
     * Prints from a particular client.
     *
     * Relationship client (1:N) prints.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'client_id', 'id');
    }
}
