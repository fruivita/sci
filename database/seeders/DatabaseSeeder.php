<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->seedAuthorizations();
    }

    /**
     * Seed perfis e suas permissões.
     *
     * @return void
     */
    public function seedAuthorizations()
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

        DB::table('permissions')->insert([
            [
                'id' => Role::VIEWANY,
                'name' => __('Role: View all'),
                'description' => __('Permission to list all roles.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => Role::VIEW,
                'name' => __('Role: View one'),
                'description' => __('Permission to view roles individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => Role::UPDATE,
                'name' => __('Role: Update one'),
                'description' => __('Permission to update roles individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => Permission::VIEWANY,
                'name' => __('Permission: View all'),
                'description' => __('Permission to list all permissions.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => Permission::UPDATE,
                'name' => __('Permission: Update one'),
                'description' => __('Permission to update permissions individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);

        DB::table('permission_role')->insert([
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => Role::VIEWANY,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => Role::VIEW,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => Role::UPDATE,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => Permission::VIEWANY,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => Permission::UPDATE,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
