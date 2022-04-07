<?php

namespace Database\Factories;

use App\Models\Department;
use FruiVita\Corporate\Database\Factories\DepartmentFactory as CorporateDepartmentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 *
 * @see https://laravel.com/docs/9.x/database-testing
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
        return (new CorporateDepartmentFactory)->definition();
    }
}
