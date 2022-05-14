<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

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
        $now = now()->format('Y-m-d H:i:s');

        DB::table('permission_role')->insert(
            $this->allRolesPermissions()
            ->map(function($item) use ($now) {
                $item['created_at'] = $now;
                $item['updated_at'] = $now;

                return $item;
            })
            ->toArray()
        );
    }

    /**
     * All roles and their respective permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function allRolesPermissions()
    {
        return $this->administratorPermissions()
        ->concat($this->institutionalManagerPermissions())
        ->concat($this->departmentManagerPermissions())
        ->concat($this->ordinaryPermissions());

    }

    /**
     * Initial administrator role permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function administratorPermissions()
    {
        return LazyCollection::make([
            PermissionType::ConfigurationView->value,
            PermissionType::ConfigurationUpdate->value,
            PermissionType::RoleViewAny->value,
            PermissionType::RoleView->value,
            PermissionType::RoleUpdate->value,
            PermissionType::PermissionViewAny->value,
            PermissionType::PermissionView->value,
            PermissionType::PermissionUpdate->value,
            PermissionType::UserViewAny->value,
            PermissionType::UserUpdate->value,
            PermissionType::SimulationCreate->value,
            PermissionType::ImportationCreate->value,
            PermissionType::DelegationViewAny->value,
            PermissionType::DelegationCreate->value,
            PermissionType::PrinterReport->value,
            PermissionType::PrintingReport->value,
            PermissionType::ServerViewAny->value,
            PermissionType::ServerView->value,
            PermissionType::ServerUpdate->value,
            PermissionType::ServerReport->value,
            PermissionType::SiteViewAny->value,
            PermissionType::SiteView->value,
            PermissionType::SiteCreate->value,
            PermissionType::SiteUpdate->value,
            PermissionType::SiteDelete->value,
            PermissionType::DepartmentReport->value,
            PermissionType::ManagerialReport->value,
            PermissionType::InstitutionalReport->value,
            PermissionType::LogViewAny->value,
            PermissionType::LogDelete->value,
            PermissionType::LogDownload->value,
            PermissionType::DocumentationViewAny->value,
            PermissionType::DocumentationCreate->value,
            PermissionType::DocumentationUpdate->value,
            PermissionType::DocumentationDelete->value,
        ])->map(function($item) {
            $new_item['role_id'] = Role::ADMINISTRATOR;
            $new_item['permission_id'] = $item;

            return $new_item;
        });
    }

    /**
     * Initial institutional manager role permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function institutionalManagerPermissions()
    {
        return LazyCollection::make([
            //...
        ])->map(function($item) {
            $new_item['role_id'] = Role::INSTITUTIONALMANAGER;
            $new_item['permission_id'] = $item;

            return $new_item;
        });
    }

    /**
     * Initial department manager role permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function departmentManagerPermissions()
    {
        return LazyCollection::make([
            //...
        ])->map(function($item) {
            $new_item['role_id'] = Role::DEPARTMENTMANAGER;
            $new_item['permission_id'] = $item;

            return $new_item;
        });
    }

    /**
     * Initial ordinary role permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function ordinaryPermissions()
    {
        return LazyCollection::make([
            //...
        ])->map(function($item) {
            $new_item['role_id'] = Role::ORDINARY;
            $new_item['permission_id'] = $item;

            return $new_item;
        });
    }
}
