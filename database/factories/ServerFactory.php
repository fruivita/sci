<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class ServerFactory extends Factory
{
    protected $model = Server::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->lexify('??????????.example.org'),
        ];
    }
}
