<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->numerify(
                random_int(0, 1)
                    ? 'cpu-#####'
                    : 'ntb-#####'
            ),
        ];
    }
}
