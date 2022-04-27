<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
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
                'id' => PermissionType::ConfigurationView->value,
                'name' => __('Application settings: View'),
                'description' => __('Permission to view application settings.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::ConfigurationUpdate->value,
                'name' => __('Application settings: Update'),
                'description' => __('Permission to update application settings.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::RoleViewAny->value,
                'name' => __('Role: View all'),
                'description' => __('Permission to view all roles.'),
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
                'description' => __('Permission to view all permissions.'),
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
                'description' => __('Permission to view all users.'),
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
            [
                'id' => PermissionType::ImportationCreate->value,
                'name' => __('Importation: Create'),
                'description' => __('Permission to request forced data import.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::DelegationViewAny->value,
                'name' => __('Delegation: View all'),
                'description' => __('Permission to view all department delegations.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::DelegationCreate->value,
                'name' => __('Delegation: Create'),
                'description' => __('Permission to delegate the role (and its permissions) to another user in the same department.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::PrinterReport->value,
                'name' => __('Report by printer: Generate'),
                'description' => __('Permission to generate the print-by-printer report.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::PrintingReport->value,
                'name' => __('General print report: Generate'),
                'description' => __('Permission to generate the general print report.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::ServerViewAny->value,
                'name' => __('Server: View all'),
                'description' => __('Permission to view all servers.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::ServerView->value,
                'name' => __('Server: View one'),
                'description' => __('Permission to view servers individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::ServerUpdate->value,
                'name' => __('Server: Update one'),
                'description' => __('Permission to update servers individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::ServerReport->value,
                'name' => __('Report by server: Generate'),
                'description' => __('Permission to generate the print-by-server report.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::SiteViewAny->value,
                'name' => __('Site: View all'),
                'description' => __('Permission to view all sites.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::SiteView->value,
                'name' => __('Site: View one'),
                'description' => __('Permission to view sites individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::SiteUpdate->value,
                'name' => __('Site: Update one'),
                'description' => __('Permission to update sites individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::DepartmentReport->value,
                'name' => __('Report by department: Generate'),
                'description' => __('Permission to generate the print-by-department report. Restricted to the authenticated user department.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::ManagerialReport->value,
                'name' => __('Report by department (Managerial): Generate'),
                'description' => __('Permission to generate the print-by-department report (child departments included). Restricted to the authenticated user department.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::InstitutionalReport->value,
                'name' => __('Report by department (Institutional): Generate'),
                'description' => __('Permission to generate the print report from all departments.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::LogViewAny->value,
                'name' => __('Log: View all'),
                'description' => __('Permission to view all application log files.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::LogDelete->value,
                'name' => __('Log: Delete one'),
                'description' => __('Permission to delete application log files individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'id' => PermissionType::LogDownload->value,
                'name' => __('Log: Download one'),
                'description' => __('Permission to download application log files individually.'),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
