<?php

namespace Database\Factories;

use App\Models\Person;
use FruiVita\Corporate\Database\Factories\PersonFactory as CorporatePersonFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return (new CorporatePersonFactory)->definition();
    }
}
