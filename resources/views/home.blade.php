{{--
    View default para usuários autenticados.

    @see https://laravel.com/docs/9.x/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-layouts.app>

    <x-page header="{{ __('Home') }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            @can(\App\Enums\Policy::Report->value, \App\Models\Printing::class)

                <x-link-card
                    icon="graph-up"
                    href="{{ route('report.printing.create') }}"
                    text="{{ __('General print report') }}"
                    title="{{ __('Go to general print report page') }}"/>

            @endcan


            @can(\App\Enums\Policy::Report->value, \App\Models\Printer::class)

                <x-link-card
                    icon="printer"
                    href="{{ route('report.printer.create') }}"
                    text="{{ __('Report by printer') }}"
                    title="{{ __('Go to report by printer page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ReportAny->value, \App\Models\Department::class)

                <x-link-card
                    icon="diagram-3"
                    href="{{ route('report.department.create') }}"
                    text="{{ __('Report by department') }}"
                    title="{{ __('Go to report by department page') }}"/>

            @endcan


            @can(\App\Enums\Policy::Report->value, \App\Models\Server::class)

                <x-link-card
                    icon="server"
                    href="{{ route('report.server.create') }}"
                    text="{{ __('Report by server') }}"
                    title="{{ __('Go to report by server page') }}"/>

            @endcan


            @can(\App\Enums\Policy::View->value, \App\Models\Configuration::class)

                <x-link-card
                    icon="gear"
                    href="{{ route('administration.configuration.show') }}"
                    text="{{ __('Application settings') }}"
                    title="{{ __('Go to application settings page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ImportationCreate->value)

                <x-link-card
                    icon="usb-drive"
                    href="{{ route('administration.importation.create') }}"
                    text="{{ __('Forced data import') }}"
                    title="{{ __('Go to data importation page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Site::class)

                <x-link-card
                    icon="building"
                    href="{{ route('administration.site.index') }}"
                    text="{{ __('Manage sites') }}"
                    title="{{ __('Go to sites page') }}"/>

            @endcan


            @can(\App\Enums\Policy::LogViewAny->value)

                <x-link-card
                    icon="file-earmark-text"
                    href="{{ route('administration.log.index') }}"
                    text="{{ __('Manage logs') }}"
                    title="{{ __('Go to application logs page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Server::class)

                <x-link-card
                    icon="server"
                    href="{{ route('administration.server.index') }}"
                    text="{{ __('Manage servers') }}"
                    title="{{ __('Go to servers page') }}"/>

            @endcan


            @can(\App\Enums\Policy::DelegationViewAny->value)

                <x-link-card
                    icon="person-lines-fill"
                    href="{{ route('authorization.delegations.index') }}"
                    text="{{ __('Role delegation') }}"
                    title="{{ __('Go to delegations page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Role::class)

                <x-link-card
                    icon="award"
                    href="{{ route('authorization.role.index') }}"
                    text="{{ __('Manage roles') }}"
                    title="{{ __('Go to roles page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\Permission::class)

                <x-link-card
                    icon="vector-pen"
                    href="{{ route('authorization.permission.index') }}"
                    text="{{ __('Manage permissions') }}"
                    title="{{ __('Go to permissions page') }}"/>

            @endcan


            @can(\App\Enums\Policy::ViewAny->value, \App\Models\User::class)

                <x-link-card
                    icon="person-check"
                    href="{{ route('authorization.user.index') }}"
                    text="{{ __('Manage users') }}"
                    title="{{ __('Go to users page') }}"/>

            @endcan


            @can(\App\Enums\Policy::SimulationCreate->value)

                <x-link-card
                    icon="people"
                    href="{{ route('test.simulation.create') }}"
                    text="{{ __('Usage simulation') }}"
                    title="{{ __('Go to simulation page') }}"/>

            @endcan

        </div>

    </x-page>

</x-layouts.app>
