<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/9.x/database-testing
 */
class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'id' => Role::ADMINISTRATOR,
                'name' => __('Administrator'),
                'description' => __('Role with access to all application operations.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => Role::INSTITUTIONALMANAGER,
                'name' => __('Institutional manager'),
                'description' => __("Role with access to all the application's business functions. Does not have access to administration functions."),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => Role::DEPARTMENTMANAGER,
                'name' => __('Department manager'),
                'description' => __("Role with access to the application's business functions restricted to the department itself. Does not have access to administration functions."),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => Role::ORDINARY,
                'name' => __('Ordinary'),
                'description' => __('Role with access to only minimal functions.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);

    }
}
