<?php

namespace Database\Factories;

use App\Models\Printer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Printer>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class PrinterFactory extends Factory
{
    protected $model = Printer::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->numerify(
                random_int(0, 1)
                    ? 'imp-#####'
                    : 'mlt-#####'
            ),
        ];
    }
}
