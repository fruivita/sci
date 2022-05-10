<?php

namespace Database\Factories;

use function App\maxSafeInteger;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, maxSafeInteger()),
            'name' => $this->faker->unique()->text(50),
            'description' => $this->faker->text(255),
        ];
    }
}
