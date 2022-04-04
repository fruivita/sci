<?php

namespace Database\Factories;

use App\Models\Occupation;
use FruiVita\Corporate\Database\Factories\OccupationFactory as CorporateOccupationFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Occupation>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class OccupationFactory extends Factory
{
    protected $model = Occupation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return (new CorporateOccupationFactory)->definition();
    }
}
