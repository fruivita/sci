<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->text(50),
        ];
    }
}
