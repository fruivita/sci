<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

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
        $now = now()->format('Y-m-d H:i:s');

        DB::table('permissions')->insert(
            $this->allPermissions()
            ->map(function ($item) use ($now) {
                $item['created_at'] = $now;
                $item['updated_at'] = $now;

                return $item;
            })
            ->toArray()
        );
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function allPermissions()
    {
        return $this->configurationPermissions()
        ->concat($this->rolePermissions())
        ->concat($this->permissionPermissions())
        ->concat($this->userPermissions())
        ->concat($this->simulationPermissions())
        ->concat($this->importationPermissions())
        ->concat($this->delegationPermissions())
        ->concat($this->printerPermissions())
        ->concat($this->printingPermissions())
        ->concat($this->serverPermissions())
        ->concat($this->sitePermissions())
        ->concat($this->departmentPermissions())
        ->concat($this->logPermissions())
        ->concat($this->documentationPermissions());
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function configurationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::ConfigurationView->value,
                'name' => __('Application settings: View'),
                'description' => __('Permission to view registered application settings.'),
            ],
            [
                'id' => PermissionType::ConfigurationUpdate->value,
                'name' => __('Application settings: Update'),
                'description' => __('Permission to update registered application settings.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function rolePermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::RoleViewAny->value,
                'name' => __('Role: View all'),
                'description' => __('Permission to view all registered roles.'),
            ],
            [
                'id' => PermissionType::RoleView->value,
                'name' => __('Role: View one'),
                'description' => __('Permission to individually view registered roles.'),
            ],
            [
                'id' => PermissionType::RoleUpdate->value,
                'name' => __('Role: Update one'),
                'description' => __('Permission to individually update registered roles.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissionPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::PermissionViewAny->value,
                'name' => __('Permission: View all'),
                'description' => __('Permission to view all registered permissions.'),
            ],
            [
                'id' => PermissionType::PermissionView->value,
                'name' => __('Permission: View one'),
                'description' => __('Permission to individually view registered permissions.'),
            ],
            [
                'id' => PermissionType::PermissionUpdate->value,
                'name' => __('Permission: Update one'),
                'description' => __('Permission to individually update registered permissions.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function userPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::UserViewAny->value,
                'name' => __('User: View all'),
                'description' => __('Permission to view all registered users.'),
            ],
            [
                'id' => PermissionType::UserUpdate->value,
                'name' => __('User: Update one'),
                'description' => __('Permission to individually update registered users.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function simulationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::SimulationCreate->value,
                'name' => __('Simulation: Create'),
                'description' => __('Permission to simulate using the application as if it were another user.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function importationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::ImportationCreate->value,
                'name' => __('Importation: Create'),
                'description' => __('Permission to request forced data import.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function delegationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::DelegationViewAny->value,
                'name' => __('Delegation: View all'),
                'description' => __('Permission to view all department delegations.'),
            ],
            [
                'id' => PermissionType::DelegationCreate->value,
                'name' => __('Delegation: Create'),
                'description' => __('Permission to delegate the role (and its permissions) to another user in the same department.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function printerPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::PrinterReport->value,
                'name' => __('Report by printer: Generate'),
                'description' => __('Permission to generate the print-by-printer report.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function printingPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::PrintingReport->value,
                'name' => __('General print report: Generate'),
                'description' => __('Permission to generate the general print report.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function serverPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::ServerViewAny->value,
                'name' => __('Server: View all'),
                'description' => __('Permission to view all registered servers.'),
            ],
            [
                'id' => PermissionType::ServerView->value,
                'name' => __('Server: View one'),
                'description' => __('Permission to individually view registered servers.'),
            ],
            [
                'id' => PermissionType::ServerUpdate->value,
                'name' => __('Server: Update one'),
                'description' => __('Permission to individually update registered servers.'),
            ],
            [
                'id' => PermissionType::ServerReport->value,
                'name' => __('Report by server: Generate'),
                'description' => __('Permission to generate the print-by-server report.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function sitePermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::SiteViewAny->value,
                'name' => __('Site: View all'),
                'description' => __('Permission to view all registered sites.'),
            ],
            [
                'id' => PermissionType::SiteView->value,
                'name' => __('Site: View one'),
                'description' => __('Permission to individually view registered sites.'),
            ],
            [
                'id' => PermissionType::SiteCreate->value,
                'name' => __('Site: Create one'),
                'description' => __('Permission to individually create sites.'),
            ],
            [
                'id' => PermissionType::SiteUpdate->value,
                'name' => __('Site: Update one'),
                'description' => __('Permission to individually update registered sites.'),
            ],
            [
                'id' => PermissionType::SiteDelete->value,
                'name' => __('Site: Delete one'),
                'description' => __('Permission to individually delete registered sites.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function departmentPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::DepartmentReport->value,
                'name' => __('Report by department: Generate'),
                'description' => __('Permission to generate the print-by-department report. Restricted to the authenticated user department.'),
            ],
            [
                'id' => PermissionType::ManagerialReport->value,
                'name' => __('Report by department (Managerial): Generate'),
                'description' => __('Permission to generate the print-by-department report (child departments included). Restricted to the authenticated user department.'),
            ],
            [
                'id' => PermissionType::InstitutionalReport->value,
                'name' => __('Report by department (Institutional): Generate'),
                'description' => __('Permission to generate the print report from all departments.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function logPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::LogViewAny->value,
                'name' => __('Log: View all'),
                'description' => __('Permission to view all application log files.'),
            ],
            [
                'id' => PermissionType::LogDelete->value,
                'name' => __('Log: Delete one'),
                'description' => __('Permission to individually delete application log files.'),
            ],
            [
                'id' => PermissionType::LogDownload->value,
                'name' => __('Log: Download one'),
                'description' => __('Permission to individually download application log files.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function documentationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::DocumentationViewAny->value,
                'name' => __('Documentation: View all'),
                'description' => __('Permission to view all registered application documentation.'),
            ],
            [
                'id' => PermissionType::DocumentationCreate->value,
                'name' => __('Documentation: Create one'),
                'description' => __('Permission to individually create application documentation.'),
            ],
            [
                'id' => PermissionType::DocumentationUpdate->value,
                'name' => __('Documentation: Update one'),
                'description' => __('Permission to individually update registered application documentation.'),
            ],
            [
                'id' => PermissionType::DocumentationDelete->value,
                'name' => __('Documentation: Delete one'),
                'description' => __('Permission to individually delete registered application documentation.'),
            ],
        ]);
    }
}
