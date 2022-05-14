<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

/**
 * @see https://laravel.com/docs/database-testing
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
        $now = now()->format('Y-m-d H:i:s');

        DB::table('roles')->insert(
            $this->allPermissions()
            ->map(function($item) use ($now) {
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
        return LazyCollection::make([
            [
                'id' => Role::ADMINISTRATOR,
                'name' => __('Administrator'),
                'description' => __('Role with access to all application operations.'),
            ],
            [
                'id' => Role::INSTITUTIONALMANAGER,
                'name' => __('Institutional manager'),
                'description' => __("Role with access to all the application's business functions. Does not have access to administration functions."),
            ],
            [
                'id' => Role::DEPARTMENTMANAGER,
                'name' => __('Department manager'),
                'description' => __("Role with access to the application's business functions restricted to the department itself. Does not have access to administration functions."),
            ],
            [
                'id' => Role::ORDINARY,
                'name' => __('Ordinary'),
                'description' => __('Role with access to only minimal functions.'),
            ],
        ]);
    }
}
