<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Configurações da aplicação.
 *
 * @see https://laravel.com/docs/eloquent
 */
class Configuration extends Model
{
    use HasFactory;

    protected $table = 'configurations';

    public $incrementing = false;

    /**
     * Id da configuração da aplicação.
     *
     * @var int
     */
    public const MAIN = 101;
}
