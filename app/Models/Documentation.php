<?php

namespace App\Models;

use Database\Factories\DocFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @see https://laravel.com/docs/eloquent
 */
class Documentation extends Model
{
    use HasFactory;

    protected $table = 'docs';

    /**
     * Ordenação padrão do modelo.
     *
     * Ordem: name app_route_name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('app_route_name', 'asc');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return DocFactory::new();
    }
}
