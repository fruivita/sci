<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permission_role')->insert([
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::RoleViewAny->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::RoleView->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::RoleUpdate->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::PermissionViewAny->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::PermissionView->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::PermissionUpdate->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::UserViewAny->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::UserUpdate->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::SimulationCreate->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::ImportationCreate->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::DelegationViewAny->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::DelegationCreate->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::PrinterReport->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::PrinterPDFReport->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::PrintingReport->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::PrintingPDFReport->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::ServerReport->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'role_id' => Role::ADMINISTRATOR,
                'permission_id' => PermissionType::ServerPDFReport->value,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
