<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Impressoras.
 *
 * @see https://laravel.com/docs/9.x/eloquent
 */
class Printer extends Model
{
    use HasFactory;

    protected $table = 'printers';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * Impressões feitas por uma determinada impressora.
     *
     * Relacionamento impressora (1:N) impressões.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prints()
    {
        return $this->hasMany(Printing::class, 'printer_id', 'id');
    }
}
