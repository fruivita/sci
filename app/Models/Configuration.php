<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Application Configurations.
 *
 * @see https://laravel.com/docs/eloquent
 */
class Configuration extends Model
{
    use HasFactory;

    protected $table = 'configurations';

    public $incrementing = false;

    /**
     * Application configuration id.
     *
     * @var int
     */
    public const MAIN = 101;
}
