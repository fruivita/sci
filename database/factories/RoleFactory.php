<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, PHP_INT_MAX),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->text(),
        ];
    }
}
