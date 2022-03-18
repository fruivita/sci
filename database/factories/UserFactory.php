<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => rand(0, 1)
                        ? null
                        : $this->faker->sentence(3),

            'username' => Str::random(20),

            'password' => null,
            'guid' => $this->faker->uuid(),
            'domain' => $this->faker->domainName(),
            'role_id' => null,
        ];
    }
}
