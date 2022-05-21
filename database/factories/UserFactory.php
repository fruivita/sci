<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use FruiVita\Corporate\Database\Factories\UserFactory as CorporateUserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return array_merge(
            (new CorporateUserFactory())->definition(),
            [
                'name' => rand(0, 1)
                        ? null
                        : $this->faker->text(50),

                'username' => $this->faker->unique()->text(20),

                'password' => null,
                'guid' => $this->faker->unique()->uuid(),
                'domain' => $this->faker->domainName(),
                'role_id' => Role::factory(),
                'department_id' => Department::DEPARTMENTLESS,
                'role_granted_by' => null,
            ]
        );
    }
}
