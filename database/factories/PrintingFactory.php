<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Printing>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class PrintingFactory extends Factory
{
    protected $model = Printing::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'client_id' => Client::factory(),
            'department_id' => null,
            'user_id' => User::factory(),
            'printer_id' => Printer::factory(),
            'server_id' => Server::factory(),

            'date' => $this->faker->date(),
            'time' => $this->faker->time(max: '23:59:59'),

            'filename' => random_int(0, 1)
                            ? $this->faker->word() . '.' . $this->faker->fileExtension()
                            : null,

            'file_size' => random_int(0, 1)
                            ? $this->faker->randomNumber(3)
                            : null,

            'pages' => random_int(1, 100),
            'copies' => random_int(1, 20),
        ];
    }
}
