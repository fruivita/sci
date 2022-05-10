<?php

namespace Database\Factories;

use App\Models\Duty;
use FruiVita\Corporate\Database\Factories\DutyFactory as CorporateDutyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Duty>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class DutyFactory extends Factory
{
    protected $model = Duty::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return (new CorporateDutyFactory)->definition();
    }
}
