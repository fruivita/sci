<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([
            [
                'id' => PermissionType::RoleViewAny->value,
                'name' => __('Role: View all'),
                'description' => __('Permission to list all roles.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::RoleView->value,
                'name' => __('Role: View one'),
                'description' => __('Permission to view roles individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::RoleUpdate->value,
                'name' => __('Role: Update one'),
                'description' => __('Permission to update roles individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::PermissionViewAny->value,
                'name' => __('Permission: View all'),
                'description' => __('Permission to list all permissions.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::PermissionView->value,
                'name' => __('Permission: View one'),
                'description' => __('Permission to view permissions individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::PermissionUpdate->value,
                'name' => __('Permission: Update one'),
                'description' => __('Permission to update permissions individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::UserViewAny->value,
                'name' => __('User: View all'),
                'description' => __('Permission to list all users.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::UserUpdate->value,
                'name' => __('User: Update one'),
                'description' => __('Permission to update users individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::SimulationCreate->value,
                'name' => __('Simulation: Create'),
                'description' => __('Permission to simulate using the application as if it were another user.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
