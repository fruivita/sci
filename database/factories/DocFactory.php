<?php

namespace Database\Factories;

use App\Models\Documentation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documentation>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class DocFactory extends Factory
{
    protected $model = Documentation::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'app_route_name' => $this->faker->unique()->text(20),
            'doc_link' => $this->faker->unique()->url(),
        ];
    }
}
