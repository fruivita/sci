<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class DocumentationSeeder extends Seeder
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

        DB::table('docs')->insert(
            $this->allDocs()
            ->map(function ($item) use ($now) {
                $item['created_at'] = $now;
                $item['updated_at'] = $now;

                return $item;
            })
            ->toArray()
        );
    }

    /**
     * All routes and their respective external documentation URL.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function allDocs()
    {
        return LazyCollection::make([
            [
                'app_route_name' => 'login',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/autentica%C3%A7%C3%A3o'
            ],
            [
                'app_route_name' => 'administration.configuration.edit',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/configura%C3%A7%C3%A3o'
            ],
            [
                'app_route_name' => 'administration.configuration.show',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/configura%C3%A7%C3%A3o'
            ],
            [
                'app_route_name' => 'administration.doc.index',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/documenta%C3%A7%C3%A3o'
            ],
            [
                'app_route_name' => 'administration.doc.create',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/documenta%C3%A7%C3%A3o'
            ],
            [
                'app_route_name' => 'administration.doc.edit',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/documenta%C3%A7%C3%A3o'
            ],
            [
                'app_route_name' => 'administration.importation.create',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/importa%C3%A7%C3%A3o'
            ],
            [
                'app_route_name' => 'administration.site.index',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/localidade'
            ],
            [
                'app_route_name' => 'administration.site.create',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/localidade'
            ],
            [
                'app_route_name' => 'administration.site.edit',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/localidade'
            ],
            [
                'app_route_name' => 'administration.site.show',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/localidade'
            ],
            [
                'app_route_name' => 'administration.log.index',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/log'
            ],
            [
                'app_route_name' => 'administration.server.index',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/servidor'
            ],
            [
                'app_route_name' => 'administration.server.edit',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/servidor'
            ],
            [
                'app_route_name' => 'administration.server.show',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/servidor'
            ],
            [
                'app_route_name' => 'authorization.delegations.index',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/delega%C3%A7%C3%A3o'
            ],
            [
                'app_route_name' => 'authorization.role.index',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/perfil'
            ],
            [
                'app_route_name' => 'authorization.role.edit',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/perfil'
            ],
            [
                'app_route_name' => 'authorization.role.show',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/perfil'
            ],
            [
                'app_route_name' => 'authorization.permission.index',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/permiss%C3%A3o'
            ],
            [
                'app_route_name' => 'authorization.permission.edit',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/permiss%C3%A3o'
            ],
            [
                'app_route_name' => 'authorization.permission.show',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/permiss%C3%A3o'
            ],
            [
                'app_route_name' => 'authorization.user.index',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/usu%C3%A1rio'
            ],
            [
                'app_route_name' => 'home',
                'doc_link' => 'https://github.com/fruivita/sci/wiki'
            ],
            [
                'app_route_name' => 'report.printing.create',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/relat%C3%B3rio'
            ],
            [
                'app_route_name' => 'report.printer.create',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/relat%C3%B3rio'
            ],
            [
                'app_route_name' => 'report.department.create',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/relat%C3%B3rio'
            ],
            [
                'app_route_name' => 'report.server.create',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/relat%C3%B3rio'
            ],
            [
                'app_route_name' => 'test.simulation.create',
                'doc_link' => 'https://github.com/fruivita/sci/wiki/simula%C3%A7%C3%A3o'
            ],
        ]);
    }
}
