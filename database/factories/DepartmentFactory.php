<?php

namespace Database\Factories;

use App\Models\Department;
use FruiVita\Corporate\Database\Factories\DepartmentFactory as CorporateDepartmentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return
        // override id generation rule
        ['id' => $this->faker->unique()->numberBetween(1)]
        + (new CorporateDepartmentFactory())->definition();
    }
}
